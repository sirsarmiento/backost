<?php

namespace App\Controller\Costo;

use App\Repository\Costo\VentaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class VentaController extends AbstractController
{
    /**
     * @Route("/api/venta", methods={"POST"})
     */
    public function post(Request $request, VentaRepository $repository): JsonResponse
    {
        return $repository->post(json_decode($request->getContent(), true) ?: []);
    }

    /**
     * @Route("/api/ventas", methods={"GET"})
     */
    public function findAll(VentaRepository $repository): JsonResponse
    {
        $data = $repository->getAll();
        return new JsonResponse(['message' => 'Ventas obtenidas', 'data' => $data, 'count' => count($data)]);
    }
}
