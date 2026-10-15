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
        if (in_array($valor, ['producto', 'productos', 'producto final', 'producto fabricado', 'pf'], true)) {
            return 'PF';
        }
        if (in_array($valor, ['proyecto', 'proyectos', 'pr', 'py', 'p'], true)) {
            return 'P';
        }

        return 'SR';
    }

    /**
     * @return array{0: string[], 1: string[]}
     */
    public function partesDesdeTexto(?string $tecnologia, ?string $material): array
    {
        $tecnologias = $this->partirValores($tecnologia);
        $materiales = $this->partirValores($material);

        return [$tecnologias, $materiales];
    }

    public function armarSegmentoTecnologia(array $tecnologias): string
    {
        $tecnologias = $this->partirValores(implode('/', $tecnologias));
        if (count($tecnologias) > 1) {
            return 'M-' . implode('/', $tecnologias);
        }

        return $tecnologias[0] ?? '';
    }

    public function armarSegmentoMaterial(array $materiales): string
    {
        $materiales = $this->partirValores(implode('/', $materiales));

        return implode('/', $materiales);
    }

    public function asignar(Producto $producto, EntityManagerInterface $em, bool $forzar = false): void
    {
        [$tecnologias, $materiales] = $this->partesDesdeTexto($producto->getTecnologia(), $producto->getMaterial());
        $segmentoTec = $this->armarSegmentoTecnologia($tecnologias);
        $segmentoMat = $this->armarSegmentoMaterial($materiales);
        $familia = $producto->getFamilia() ? strtoupper(trim((string) $producto->getFamilia()->getCodigo())) : '';
        $categoria = $this->categoriaDesdeClasificacion($producto->getClasificacion());
        $esProyecto = $categoria === 'P';

        if ($segmentoTec === '') {
            return;
        }
        if (!$esProyecto && ($segmentoMat === '' || $familia === '')) {
            return;
        }

        $correlativo = $this->resolverCorrelativo($producto, $em, $familia, $forzar);

        $producto->setCorrelativo($correlativo);
        if ($esProyecto) {
            $producto->setSku(sprintf('P-%s-%s', $correlativo, implode('/', $tecnologias)));
            $producto->setCodigoCatalogo(sprintf('P-%s', $correlativo));
            return;
        }

        $producto->setSku(sprintf('%s-%s-%s-%s-%s', $categoria, $segmentoTec, $segmentoMat, $familia, $correlativo));
        $producto->setCodigoCatalogo(sprintf('%s-%s', $familia, $correlativo));
    }

    private function partirValores(?string $valor): array
    {
        $partes = preg_split('/[\\/,|-]+/', strtoupper(trim((string) $valor))) ?: [];
        $limpios = [];
        foreach ($partes as $parte) {
            $parte = trim($parte);
            if ($parte === '' || $parte === 'M') {
                continue;
            }
            if (!in_array($parte, $limpios, true)) {
                $limpios[] = $parte;
            }
        }

        return $limpios;
    }

    private function resolverCorrelativo(
        Producto $producto,
        EntityManagerInterface $em,
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
        $esProyecto = in_array($clasificacion, ['proyecto', 'proyectos', 'pr', 'py', 'p'], true);
        $serie = strtoupper(trim((string) $producto->getSerie()));

        if ($esProyecto) {
            return $this->siguientePrefijo($em, $producto, $familia, 'P', 2);
        }

        if ($serie !== '' && preg_match('/^[A-Z]{1,3}$/', $serie)) {
            return $this->siguientePrefijo($em, $producto, $familia, $serie, 2);
        }

        return $this->siguienteNumerico($em, $familia);
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
        $qb = $em->getRepository(Producto::class)->createQueryBuilder('p')
            ->select('p.correlativo')
            ->leftJoin('p.familia', 'f')
            ->where('p.correlativo LIKE :pref')
            ->setParameter('pref', $prefijo . '%');

        if ($familia !== '') {
            $qb->andWhere('UPPER(f.codigo) = :fam')->setParameter('fam', $familia);
        }

        $existentes = $qb->getQuery()->getScalarResult();

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

    private function siguienteNumerico(EntityManagerInterface $em, string $familia): string
    {
        $existentes = $em->getRepository(Producto::class)->createQueryBuilder('p')
            ->select('p.correlativo')
            ->leftJoin('p.familia', 'f')
            ->where('UPPER(f.codigo) = :fam')
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
