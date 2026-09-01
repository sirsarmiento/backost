<?php

namespace App\Service;

use App\Entity\Costo\Activo;
use App\Entity\Costo\Compra;
use App\Entity\Costo\CompraLinea;
use App\Entity\Costo\Desacople;
use App\Entity\Costo\DesacopleLinea;
use App\Entity\Costo\MovimientoInventario;
use App\Entity\Costo\Piezas;
use App\Entity\Costo\PiezasProducto;
use App\Entity\Costo\Presupuesto;
use App\Entity\Costo\Producto;
use App\Entity\Costo\Venta;
use App\Entity\Empresa;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class InventarioService
{
    private $em;
    private $security;

    public function __construct(EntityManagerInterface $em, Security $security)
    {
        $this->em = $em;
        $this->security = $security;
    }

    public function esInventario($tipo): bool
    {
        $t = strtolower(trim((string) $tipo));
        return $t === 'del inventario' || $t === 'inventario';
    }

    public function disponible(Activo $activo): float
    {
        return (float) $activo->getCantidad() - (float) $activo->getCantidadReservada();
    }

    public function registrarMovimiento(
        ?Activo $activo,
        ?Producto $producto,
        string $tipo,
        float $cantidad,
        string $referenciaTipo,
        ?int $referenciaId,
        string $observacion = ''
    ): MovimientoInventario {
        $mov = new MovimientoInventario();
        $mov->setActivo($activo);
        $mov->setProducto($producto);
        $mov->setTipo($tipo);
        $mov->setCantidad($cantidad);
        $mov->setReferenciaTipo($referenciaTipo);
        $mov->setReferenciaId($referenciaId);
        $mov->setObservacion($observacion);
        $user = $this->currentUser();
        if ($user) {
            $mov->setCreateBy($user->getUserName());
            $empresa = $this->em->getRepository(Empresa::class)->find($user->getIdempresa());
            if ($empresa) {
                $mov->setEmpresa($empresa);
            }
        }
        $this->em->persist($mov);
        return $mov;
    }

    public function recalcularReservas(): void
    {
        $activos = $this->em->getRepository(Activo::class)->findAll();
        $reservas = [];
        foreach ($activos as $activo) {
            $reservas[$activo->getId()] = 0.0;
        }

        foreach ($this->em->getRepository(PiezasProducto::class)->findAll() as $pieza) {
            if (!$this->esInventario($pieza->getTipo()) || !$pieza->getActivo()) {
                continue;
            }
            $id = $pieza->getActivo()->getId();
            $reservas[$id] = ($reservas[$id] ?? 0) + (float) $pieza->getCantidad();
        }

        foreach ($this->em->getRepository(Presupuesto::class)->findAll() as $presupuesto) {
            $estado = strtolower((string) $presupuesto->getEstado());
            if ($estado !== 'borrador' && $estado !== '') {
                continue;
            }
            $factor = max(1, (int) $presupuesto->getCantidadGlobal());
            foreach ($presupuesto->getPiezas() as $pieza) {
                if (!$this->esInventario($pieza->getTipo()) || !$pieza->getActivo()) {
                    continue;
                }
                $id = $pieza->getActivo()->getId();
                $reservas[$id] = ($reservas[$id] ?? 0) + ((float) $pieza->getCantidad() * $factor);
            }
        }

        foreach ($activos as $activo) {
            $activo->setCantidadReservada($reservas[$activo->getId()] ?? 0);
        }
    }

    public function aplicarCompra(Compra $compra): void
    {
        foreach ($compra->getLineas() as $linea) {
            $activo = $linea->getActivo();
            if (!$activo) {
                continue;
            }
            $qty = (float) $linea->getCantidad();
            $precio = (float) $linea->getValorUnitario();
            $oldQty = (float) $activo->getCantidad();
            $oldVal = (float) $activo->getValorUnitario();
            $newQty = $oldQty + $qty;
            if ($newQty > 0 && $precio > 0) {
                $nuevoUnitario = (($oldQty * $oldVal) + ($qty * $precio)) / $newQty;
                $activo->setValorUnitario($nuevoUnitario);
            }
            $activo->setCantidad($newQty);
            $tipo = strtolower((string) $activo->getTipo());
            if ($tipo === 'circulante' || $tipo === 'material') {
                $activo->setCostoInicial(((float) $activo->getValorUnitario()) * $newQty);
            }
            $this->registrarMovimiento($activo, null, 'entrada', $qty, 'compra', $compra->getId(), 'Compra a proveedor');
        }
    }

    public function aplicarVenta(Presupuesto $presupuesto, ?float $cantidadVenta = null): Venta
    {
        $estado = strtolower((string) $presupuesto->getEstado());
        if ($estado === 'vendido') {
            throw new \RuntimeException('El presupuesto ya fue convertido en venta');
        }
        if ($estado === 'anulado') {
            throw new \RuntimeException('No se puede vender un presupuesto anulado');
        }

        $factor = $cantidadVenta !== null ? $cantidadVenta : max(1, (float) $presupuesto->getCantidadGlobal());
        $producto = $presupuesto->getProducto();
        $vendioDesdeStock = false;

        if ($producto && $this->esCatalogo($producto->getClasificacion())) {
            $stock = (float) $producto->getCantidadStock();
            if ($stock >= $factor) {
                $producto->setCantidadStock($stock - $factor);
                $this->registrarMovimiento(null, $producto, 'salida_pt', $factor, 'venta', $presupuesto->getId(), 'Salida de producto terminado');
                $vendioDesdeStock = true;
            }
        }

        if (!$vendioDesdeStock) {
            $this->consumirPiezas($presupuesto->getPiezas(), $factor, 'venta', null);
        }

        $venta = new Venta();
        $venta->setPresupuesto($presupuesto);
        $venta->setCliente($presupuesto->getCliente());
        $venta->setProducto($producto);
        $venta->setCantidad($factor);
        $venta->setTotal((float) $presupuesto->getTotal());
        $venta->setFecha(new \DateTime());
        $venta->setNumero('V-' . ($presupuesto->getNumero() ?: $presupuesto->getId()));
        $venta->setDescripcion($presupuesto->getDescripcion());
        $user = $this->currentUser();
        if ($user) {
            $venta->setCreateBy($user->getUserName());
            $empresa = $this->em->getRepository(Empresa::class)->find($user->getIdempresa());
            if ($empresa) {
                $venta->setEmpresa($empresa);
            }
        }
        $this->em->persist($venta);
        $presupuesto->setEstado('vendido');
        $this->em->flush();

        if (!$vendioDesdeStock) {
            foreach ($presupuesto->getPiezas() as $pieza) {
                if ($this->esInventario($pieza->getTipo()) && $pieza->getActivo()) {
                    $qty = (float) $pieza->getCantidad() * $factor;
                    $this->registrarMovimiento($pieza->getActivo(), $producto, 'salida', $qty, 'venta', $venta->getId(), 'Descuento por venta');
                }
            }
        }

        $this->recalcularReservas();
        return $venta;
    }

    public function ingresarStock(Producto $producto, float $cantidad): void
    {
        if ($cantidad <= 0) {
            throw new \InvalidArgumentException('La cantidad debe ser mayor a 0');
        }
        $this->consumirPiezas($producto->getPiezasProducto(), $cantidad, 'ingreso_stock', $producto->getId());
        $producto->setCantidadStock((float) $producto->getCantidadStock() + $cantidad);
        $this->registrarMovimiento(null, $producto, 'entrada_pt', $cantidad, 'stock', $producto->getId(), 'Ingreso a stock de producto terminado');
        $this->recalcularReservas();
    }

    public function aplicarDesacople(Desacople $desacople): void
    {
        $producto = $desacople->getProducto();
        $qtyPt = (float) $desacople->getCantidadProducto();
        if ($producto) {
            $stock = (float) $producto->getCantidadStock();
            if ($stock < $qtyPt) {
                throw new \RuntimeException('Stock insuficiente para desacoplar');
            }
            $producto->setCantidadStock($stock - $qtyPt);
            $this->registrarMovimiento(null, $producto, 'salida_pt', $qtyPt, 'desacople', $desacople->getId(), 'Desacople de producto terminado');
        }

        foreach ($desacople->getLineas() as $linea) {
            $activo = $linea->getActivo();
            if (!$activo) {
                continue;
            }
            $recuperado = (float) $linea->getRecuperado();
            $merma = (float) $linea->getMerma();
            if ($recuperado > 0) {
                $nuevo = (float) $activo->getCantidad() + $recuperado;
                $activo->setCantidad($nuevo);
                $tipo = strtolower((string) $activo->getTipo());
                if ($tipo === 'circulante' || $tipo === 'material') {
                    $activo->setCostoInicial(((float) $activo->getValorUnitario()) * $nuevo);
                }
                $this->registrarMovimiento($activo, $producto, 'desacople', $recuperado, 'desacople', $desacople->getId(), 'Material recuperado');
            }
            if ($merma > 0) {
                $this->registrarMovimiento($activo, $producto, 'merma', $merma, 'desacople', $desacople->getId(), 'Pérdida no recuperable');
            }
        }
    }

    /**
     * @param iterable $piezas
     */
    private function consumirPiezas($piezas, float $factor, string $refTipo, ?int $refId): void
    {
        foreach ($piezas as $pieza) {
            if (!$this->esInventario($pieza->getTipo()) || !$pieza->getActivo()) {
                continue;
            }
            $activo = $pieza->getActivo();
            $need = (float) $pieza->getCantidad() * $factor;
            if ($need <= 0) {
                continue;
            }
            $stock = (float) $activo->getCantidad();
            if ($stock + 0.0001 < $need) {
                throw new \RuntimeException('Stock insuficiente de ' . $activo->getNombre() . '. Disponible: ' . $stock . ', requerido: ' . $need);
            }
            $nuevo = $stock - $need;
            $activo->setCantidad($nuevo);
            $tipo = strtolower((string) $activo->getTipo());
            if ($tipo === 'circulante' || $tipo === 'material') {
                $activo->setCostoInicial(((float) $activo->getValorUnitario()) * $nuevo);
            }
            if ($refTipo !== 'venta') {
                $this->registrarMovimiento($activo, null, 'salida', $need, $refTipo, $refId, 'Consumo de inventario');
            }
        }
    }

    private function esCatalogo(?string $clasificacion): bool
    {
        $c = strtolower(trim((string) $clasificacion));
        return $c === 'producto' || $c === 'productos';
    }

    private function currentUser(): ?User
    {
        $user = $this->security->getUser();
        return $user instanceof User ? $user : null;
    }
}
