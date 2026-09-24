<?php

namespace App\Service\Costo;

use App\Entity\Costo\Producto;
use Doctrine\ORM\EntityManagerInterface;

class SkuProductoService
{
    private const CATALOGO_LUD = [
        'tetris balance' => 'JM01',
        'isla de pascua' => 'JM02',
        'llegaron las pizzas' => 'JM03',
        'torre de rocas' => 'JM04',
        'rock balance' => 'JM05',
        'the wall' => 'JM06',
        'la cueva' => 'JM07',
        'quoridor' => 'JM08',
        'triggle' => 'JM09',
        'juego de ajedrez minimalista' => 'JM10',
        'mastermind' => 'JM11',
        'conecta 4-tex' => 'JM12',
        'conecta 4 tex' => 'JM12',
        'zigzag' => 'JM13',
        'memory color' => 'JM14',
        'laberinto mini' => 'JM15',
        'rompecabeza hexagonal' => 'RC01',
        'rompecabeza de colores' => 'RC02',
        'tetris box' => 'RC03',
        'curvas locas' => 'RC04',
        'botones locos' => 'FG01',
        'penta spin' => 'FG02',
        'heart gear' => 'FG03',
        'cube gear' => 'FG04',
        'sevoya flexi 1' => 'FG05',
        'sevoya flexi 2' => 'FG06',
        'brain flexi' => 'FG07',
        'cono fidget' => 'FG08',
        'tuerca infinita' => 'FG09',
        'torre flexi' => 'FG10',
    ];

    public function categoriaDesdeClasificacion(?string $clasificacion): string
    {
        $valor = strtolower(trim((string) $clasificacion));
        if (in_array($valor, ['producto', 'productos', 'producto final', 'producto fabricado'], true)) {
            return 'PF';
        }

        return 'SR';
    }

    public function asignar(Producto $producto, EntityManagerInterface $em, bool $forzar = false): void
    {
        $tecnologia = strtoupper(trim((string) $producto->getTecnologia()));
        $material = strtoupper(trim((string) $producto->getMaterial()));
        $familia = $producto->getFamilia() ? strtoupper(trim((string) $producto->getFamilia()->getCodigo())) : '';

        if ($tecnologia === '' || $material === '' || $familia === '') {
            return;
        }

        $categoria = $this->categoriaDesdeClasificacion($producto->getClasificacion());
        $correlativo = $this->resolverCorrelativo($producto, $em, $categoria, $tecnologia, $material, $familia, $forzar);

        $producto->setCorrelativo($correlativo);
        $producto->setSku(sprintf('%s-%s-%s-%s-%s', $categoria, $tecnologia, $material, $familia, $correlativo));
        $producto->setCodigoCatalogo(sprintf('%s-%s', $familia, $correlativo));
    }

    private function resolverCorrelativo(
        Producto $producto,
        EntityManagerInterface $em,
        string $categoria,
        string $tecnologia,
        string $material,
        string $familia,
        bool $forzar
    ): string {
        if (!$forzar && $producto->getCorrelativo()) {
            return strtoupper($producto->getCorrelativo());
        }

        $nombre = strtolower(trim((string) $producto->getNombre()));
        if ($familia === 'LUD' && isset(self::CATALOGO_LUD[$nombre])) {
            $oficial = self::CATALOGO_LUD[$nombre];
            if ($this->correlativoLibre($em, $oficial, $producto->getId())) {
                return $oficial;
            }
        }

        $clasificacion = strtolower(trim((string) $producto->getClasificacion()));
        $esProyecto = in_array($clasificacion, ['proyecto', 'proyectos'], true);
        $serie = strtoupper(trim((string) $producto->getSerie()));

        if ($esProyecto) {
            return $this->siguientePrefijo($em, $producto, $familia, 'P', 2);
        }

        if ($serie !== '' && preg_match('/^[A-Z]{1,3}$/', $serie)) {
            return $this->siguientePrefijo($em, $producto, $familia, $serie, 2);
        }

        return $this->siguienteNumerico($em, $tecnologia, $material, $familia);
    }

    private function correlativoLibre(EntityManagerInterface $em, string $correlativo, ?int $ignorarId): bool
    {
        $qb = $em->getRepository(Producto::class)->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.correlativo = :corr')
            ->setParameter('corr', $correlativo);

        if ($ignorarId) {
            $qb->andWhere('p.id != :id')->setParameter('id', $ignorarId);
        }

        return ((int) $qb->getQuery()->getSingleScalarResult()) === 0;
    }

    private function siguientePrefijo(EntityManagerInterface $em, Producto $producto, string $familia, string $prefijo, int $digitos): string
    {
        $existentes = $em->getRepository(Producto::class)->createQueryBuilder('p')
            ->select('p.correlativo')
            ->leftJoin('p.familia', 'f')
            ->where('UPPER(f.codigo) = :fam')
            ->andWhere('p.correlativo LIKE :pref')
            ->setParameter('fam', $familia)
            ->setParameter('pref', $prefijo . '%')
            ->getQuery()
            ->getScalarResult();

        $max = 0;
        $patron = '/^' . preg_quote($prefijo, '/') . '(\d+)$/';
        foreach ($existentes as $fila) {
            $valor = strtoupper((string) ($fila['correlativo'] ?? ''));
            if ($producto->getId() && $producto->getCorrelativo() === $valor) {
                continue;
            }
            if (preg_match($patron, $valor, $m)) {
                $max = max($max, (int) $m[1]);
            }
        }

        return $prefijo . str_pad((string) ($max + 1), $digitos, '0', STR_PAD_LEFT);
    }

    private function siguienteNumerico(
        EntityManagerInterface $em,
        string $tecnologia,
        string $material,
        string $familia
    ): string {
        $existentes = $em->getRepository(Producto::class)->createQueryBuilder('p')
            ->select('p.correlativo')
            ->leftJoin('p.familia', 'f')
            ->where('p.tecnologia = :tec')
            ->andWhere('p.material = :mat')
            ->andWhere('UPPER(f.codigo) = :fam')
            ->setParameter('tec', $tecnologia)
            ->setParameter('mat', $material)
            ->setParameter('fam', $familia)
            ->getQuery()
            ->getScalarResult();

        $max = 0;
        foreach ($existentes as $fila) {
            $valor = strtoupper((string) ($fila['correlativo'] ?? ''));
            if (preg_match('/^\d{3}$/', $valor)) {
                $max = max($max, (int) $valor);
            }
        }

        return str_pad((string) ($max + 1), 3, '0', STR_PAD_LEFT);
    }
}
