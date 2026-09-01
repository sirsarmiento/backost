<?php

namespace App\Controller\Costo;

use App\Repository\Costo\CompraRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CompraController extends AbstractController
{
    /**
     * @Route("/api/compra", methods={"POST"})
     */
    public function post(Request $request, CompraRepository $repository): JsonResponse
    {
        return $repository->post(json_decode($request->getContent(), true) ?: []);
    }

    /**
     * @Route("/api/compras", methods={"GET"})
     */
    public function findAll(CompraRepository $repository): JsonResponse
    {
        $data = $repository->getAll();
        return new JsonResponse(['message' => 'Compras obtenidas', 'data' => $data, 'count' => count($data)]);
    }
}
