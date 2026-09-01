<?php

namespace App\Repository\Costo;

use App\Entity\Costo\Activo;
use App\Entity\Costo\Compra;
use App\Entity\Costo\CompraLinea;
use App\Entity\Costo\Proveedor;
use App\Entity\Empresa;
use App\Entity\User;
use App\Service\InventarioService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;

class CompraRepository extends ServiceEntityRepository
{
    private $security;
    private $inventario;

    public function __construct(ManagerRegistry $registry, Security $security, InventarioService $inventario)
    {
        $this->security = $security;
        $this->inventario = $inventario;
        parent::__construct($registry, Compra::class);
    }

    public function post($data): JsonResponse
    {
        $em = $this->getEntityManager();
        try {
            if (empty($data['proveedor']) || empty($data['lineas']) || !is_array($data['lineas'])) {
                return new JsonResponse(['msg' => 'Proveedor y líneas son requeridos'], 400);
            }
            $proveedor = $em->getRepository(Proveedor::class)->find($data['proveedor']);
            if (!$proveedor) {
                return new JsonResponse(['msg' => 'Proveedor no encontrado'], 404);
            }

            $compra = new Compra();
            $compra->setProveedor($proveedor);
            $compra->setNumero($data['numero'] ?? ('C-' . date('YmdHis')));
            $compra->setFecha(!empty($data['fecha']) ? new \DateTime($data['fecha']) : new \DateTime());
            $compra->setObservacion($data['observacion'] ?? null);

            $user = $em->getRepository(User::class)->find($this->security->getUser()->getId());
            if ($user) {
                $compra->setCreateBy($user->getUserName());
                $empresa = $em->getRepository(Empresa::class)->find($user->getIdempresa());
                if ($empresa) {
                    $compra->setEmpresa($empresa);
                }
            }

            $total = 0;
            foreach ($data['lineas'] as $item) {
                $activo = $em->getRepository(Activo::class)->find($item['activo'] ?? 0);
                if (!$activo) {
                    return new JsonResponse(['msg' => 'Activo no encontrado en una línea'], 404);
                }
                $linea = new CompraLinea();
                $linea->setActivo($activo);
                $linea->setCantidad((float) ($item['cantidad'] ?? 0));
                $linea->setValorUnitario((float) ($item['valorUnitario'] ?? $activo->getValorUnitario()));
                $compra->addLinea($linea);
                $total += (float) $linea->getCantidad() * (float) $linea->getValorUnitario();
            }
            $compra->setTotal($total);

            $em->persist($compra);
            $em->flush();
            $this->inventario->aplicarCompra($compra);
            $em->flush();

            return new JsonResponse(['msg' => 'Compra registrada', 'id' => $compra->getId(), 'total' => $total], 201);
        } catch (\Exception $e) {
            return new JsonResponse(['msg' => 'Error interno', 'error' => $e->getMessage()], 500);
        }
    }

    public function getAll(): array
    {
        $result = [];
        foreach ($this->findBy([], ['id' => 'DESC']) as $c) {
            $lineas = [];
            foreach ($c->getLineas() as $l) {
                $lineas[] = [
                    'id' => $l->getId(),
                    'activo' => $l->getActivo() ? ['id' => $l->getActivo()->getId(), 'nombre' => $l->getActivo()->getNombre()] : null,
                    'cantidad' => $l->getCantidad(),
                    'valorUnitario' => $l->getValorUnitario(),
                ];
            }
            $result[] = [
                'id' => $c->getId(),
                'numero' => $c->getNumero(),
                'fecha' => $c->getFecha() ? $c->getFecha()->format('Y-m-d') : null,
                'observacion' => $c->getObservacion(),
                'total' => $c->getTotal(),
                'proveedor' => $c->getProveedor() ? [
                    'id' => $c->getProveedor()->getId(),
                    'nombre' => $c->getProveedor()->getNombre(),
                ] : null,
                'lineas' => $lineas,
            ];
        }
        return $result;
    }
}
