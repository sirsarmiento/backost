<?php

namespace App\Repository\Costo;

use App\Entity\Costo\MovimientoInventario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MovimientoInventarioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MovimientoInventario::class);
    }

    public function getAll(): array
    {
        $result = [];
        foreach ($this->findBy([], ['id' => 'DESC'], 300) as $m) {
            $result[] = [
                'id' => $m->getId(),
                'tipo' => $m->getTipo(),
                'cantidad' => $m->getCantidad(),
                'referenciaTipo' => $m->getReferenciaTipo(),
                'referenciaId' => $m->getReferenciaId(),
                'observacion' => $m->getObservacion(),
                'createAt' => $m->getCreateAt() ? $m->getCreateAt()->format('Y-m-d H:i:s') : null,
                'createBy' => $m->getCreateBy(),
                'activo' => $m->getActivo() ? [
                    'id' => $m->getActivo()->getId(),
                    'nombre' => $m->getActivo()->getNombre(),
                ] : null,
                'producto' => $m->getProducto() ? [
                    'id' => $m->getProducto()->getId(),
                    'nombre' => $m->getProducto()->getNombre(),
                ] : null,
            ];
        }
        return $result;
    }
}
