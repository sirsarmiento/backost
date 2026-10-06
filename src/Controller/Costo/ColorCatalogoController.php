<?php

namespace App\Controller\Costo;

use App\Repository\Costo\ColorCatalogoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ColorCatalogoController extends AbstractController
{
    /**
     * @Route("/api/colores", methods={"GET"})
     */
    public function findAll(ColorCatalogoRepository $repository): JsonResponse
    {
        return new JsonResponse($repository->getAll());
    }

    /**
     * @Route("/api/color", methods={"POST"})
     */
    public function post(Request $request, ColorCatalogoRepository $repository): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?: [];
        return $repository->post($data);
    }

    /**
     * @Route("/api/color/{id}", methods={"PUT"})
     */
    public function put(int $id, Request $request, ColorCatalogoRepository $repository): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?: [];
        return $repository->updateItem($id, $data);
    }

    /**
     * @Route("/api/color/{id}", methods={"DELETE"})
     */
    public function delete(int $id, ColorCatalogoRepository $repository): JsonResponse
    {
        return $repository->deleteItem($id);
    }
}
