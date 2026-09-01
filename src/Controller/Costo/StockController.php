<?php

namespace App\Controller\Costo;

use App\Entity\Costo\Producto;
use App\Repository\Costo\DesacopleRepository;
use App\Repository\Costo\MovimientoInventarioRepository;
use App\Service\InventarioService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class StockController extends AbstractController
{
    /**
     * @Route("/api/stock", methods={"GET"})
     */
    public function stock(EntityManagerInterface $em): JsonResponse
    {
        $data = [];
        foreach ($em->getRepository(Producto::class)->findAll() as $p) {
            $cls = strtolower(trim((string) $p->getClasificacion()));
            if ($cls && $cls !== 'producto' && $cls !== 'productos') {
                continue;
            }
            $data[] = [
                'id' => $p->getId(),
                'nombre' => $p->getNombre(),
                'sku' => $p->getSku(),
                'clasificacion' => $p->getClasificacion(),
                'cantidadStock' => $p->getCantidadStock(),
            ];
        }
        return new JsonResponse(['message' => 'Stock obtenido', 'data' => $data, 'count' => count($data)]);
    }

    /**
     * @Route("/api/stock/ingreso", methods={"POST"})
     */
    public function ingreso(Request $request, EntityManagerInterface $em, InventarioService $inventario): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true) ?: [];
            $producto = $em->getRepository(Producto::class)->find($data['producto'] ?? 0);
            if (!$producto) {
                return new JsonResponse(['msg' => 'Producto no encontrado'], 404);
            }
            $inventario->ingresarStock($producto, (float) ($data['cantidad'] ?? 0));
            $em->flush();
            return new JsonResponse(['msg' => 'Ingreso registrado', 'cantidadStock' => $producto->getCantidadStock()], 201);
        } catch (\Exception $e) {
            return new JsonResponse(['msg' => $e->getMessage()], 400);
        }
    }

    /**
     * @Route("/api/movimientos", methods={"GET"})
     */
    public function movimientos(MovimientoInventarioRepository $repository): JsonResponse
    {
        $data = $repository->getAll();
        return new JsonResponse(['message' => 'Movimientos obtenidos', 'data' => $data, 'count' => count($data)]);
    }

    /**
     * @Route("/api/desacople", methods={"POST"})
     */
    public function desacople(Request $request, DesacopleRepository $repository): JsonResponse
    {
        return $repository->post(json_decode($request->getContent(), true) ?: []);
    }

    /**
     * @Route("/api/desacoples", methods={"GET"})
     */
    public function desacoples(DesacopleRepository $repository): JsonResponse
    {
        $data = $repository->getAll();
        return new JsonResponse(['message' => 'Desacoples obtenidos', 'data' => $data, 'count' => count($data)]);
    }
}
