<?php

namespace App\Repository\Costo;

use App\Entity\Costo\Presupuesto;
use App\Entity\Costo\Venta;
use App\Service\InventarioService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;

class VentaRepository extends ServiceEntityRepository
{
    private $inventario;

    public function __construct(ManagerRegistry $registry, InventarioService $inventario)
    {
        $this->inventario = $inventario;
        parent::__construct($registry, Venta::class);
    }

    public function post($data): JsonResponse
    {
        try {
            if (empty($data['presupuesto'])) {
                return new JsonResponse(['msg' => 'presupuesto es requerido'], 400);
            }
            $presupuesto = $this->getEntityManager()->getRepository(Presupuesto::class)->find($data['presupuesto']);
            if (!$presupuesto) {
                return new JsonResponse(['msg' => 'Presupuesto no encontrado'], 404);
            }
            $cantidad = isset($data['cantidad']) ? (float) $data['cantidad'] : null;
            $venta = $this->inventario->aplicarVenta($presupuesto, $cantidad);
            $this->getEntityManager()->flush();
            return new JsonResponse(['msg' => 'Venta registrada', 'id' => $venta->getId(), 'numero' => $venta->getNumero()], 201);
        } catch (\RuntimeException $e) {
            return new JsonResponse(['msg' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return new JsonResponse(['msg' => 'Error interno', 'error' => $e->getMessage()], 500);
        }
    }

    public function getAll(): array
    {
        $result = [];
        foreach ($this->findBy([], ['id' => 'DESC']) as $v) {
            $result[] = [
                'id' => $v->getId(),
                'numero' => $v->getNumero(),
                'fecha' => $v->getFecha() ? $v->getFecha()->format('Y-m-d') : null,
                'descripcion' => $v->getDescripcion(),
                'cantidad' => $v->getCantidad(),
                'total' => $v->getTotal(),
                'presupuesto' => $v->getPresupuesto() ? $v->getPresupuesto()->getId() : null,
                'cliente' => $v->getCliente() ? [
                    'id' => $v->getCliente()->getId(),
                    'nombre' => $v->getCliente()->getNombre() . ' ' . $v->getCliente()->getApellido(),
                ] : null,
                'producto' => $v->getProducto() ? [
                    'id' => $v->getProducto()->getId(),
                    'nombre' => $v->getProducto()->getNombre(),
                ] : null,
            ];
        }
        return $result;
    }
}
