<?php

namespace App\Controller\Costo;

use App\Repository\Costo\MaterialCatalogoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MaterialCatalogoController extends AbstractController
{
    /**
     * @Route("/api/materiales-catalogo", methods={"GET"})
     */
    public function findAll(MaterialCatalogoRepository $repository): JsonResponse
    {
        return new JsonResponse($repository->getAll());
    }

    /**
     * @Route("/api/material-catalogo", methods={"POST"})
     */
    public function post(Request $request, MaterialCatalogoRepository $repository): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?: [];
        return $repository->post($data);
    }

    /**
     * @Route("/api/material-catalogo/{id}", methods={"PUT"})
     */
    public function put(int $id, Request $request, MaterialCatalogoRepository $repository): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?: [];
        return $repository->updateItem($id, $data);
    }

    /**
     * @Route("/api/material-catalogo/{id}", methods={"DELETE"})
     */
    public function delete(int $id, MaterialCatalogoRepository $repository): JsonResponse
    {
        return $repository->deleteItem($id);
    }
}
