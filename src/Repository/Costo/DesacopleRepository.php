<?php

namespace App\Repository\Costo;

use App\Entity\Costo\Activo;
use App\Entity\Costo\Desacople;
use App\Entity\Costo\DesacopleLinea;
use App\Entity\Costo\Producto;
use App\Entity\Empresa;
use App\Entity\User;
use App\Service\InventarioService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;

class DesacopleRepository extends ServiceEntityRepository
{
    private $security;
    private $inventario;

    public function __construct(ManagerRegistry $registry, Security $security, InventarioService $inventario)
    {
        $this->security = $security;
        $this->inventario = $inventario;
        parent::__construct($registry, Desacople::class);
    }

    public function post($data): JsonResponse
    {
        $em = $this->getEntityManager();
        try {
            if (empty($data['producto']) || empty($data['lineas'])) {
                return new JsonResponse(['msg' => 'producto y lineas son requeridos'], 400);
            }
            $producto = $em->getRepository(Producto::class)->find($data['producto']);
            if (!$producto) {
                return new JsonResponse(['msg' => 'Producto no encontrado'], 404);
            }

            $desacople = new Desacople();
            $desacople->setProducto($producto);
            $desacople->setCantidadProducto((float) ($data['cantidadProducto'] ?? 1));
            $desacople->setObservacion($data['observacion'] ?? null);
            $user = $em->getRepository(User::class)->find($this->security->getUser()->getId());
            if ($user) {
                $desacople->setCreateBy($user->getUserName());
                $empresa = $em->getRepository(Empresa::class)->find($user->getIdempresa());
                if ($empresa) {
                    $desacople->setEmpresa($empresa);
                }
            }

            foreach ($data['lineas'] as $item) {
                $activo = $em->getRepository(Activo::class)->find($item['activo'] ?? 0);
                if (!$activo) {
                    continue;
                }
                $linea = new DesacopleLinea();
                $linea->setActivo($activo);
                $linea->setRecuperado((float) ($item['recuperado'] ?? 0));
                $linea->setMerma((float) ($item['merma'] ?? 0));
                $desacople->addLinea($linea);
            }

            $em->persist($desacople);
            $em->flush();
            $this->inventario->aplicarDesacople($desacople);
            $em->flush();
            return new JsonResponse(['msg' => 'Desacople registrado', 'id' => $desacople->getId()], 201);
        } catch (\RuntimeException $e) {
            return new JsonResponse(['msg' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return new JsonResponse(['msg' => 'Error interno', 'error' => $e->getMessage()], 500);
        }
    }

    public function getAll(): array
    {
        $result = [];
        foreach ($this->findBy([], ['id' => 'DESC']) as $d) {
            $lineas = [];
            foreach ($d->getLineas() as $l) {
                $lineas[] = [
                    'activo' => $l->getActivo() ? ['id' => $l->getActivo()->getId(), 'nombre' => $l->getActivo()->getNombre()] : null,
                    'recuperado' => $l->getRecuperado(),
                    'merma' => $l->getMerma(),
                ];
            }
            $result[] = [
                'id' => $d->getId(),
                'cantidadProducto' => $d->getCantidadProducto(),
                'observacion' => $d->getObservacion(),
                'createAt' => $d->getCreateAt() ? $d->getCreateAt()->format('Y-m-d H:i:s') : null,
                'producto' => $d->getProducto() ? [
                    'id' => $d->getProducto()->getId(),
                    'nombre' => $d->getProducto()->getNombre(),
                ] : null,
                'lineas' => $lineas,
            ];
        }
        return $result;
    }
}
