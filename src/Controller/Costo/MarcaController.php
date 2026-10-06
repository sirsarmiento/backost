<?php

namespace App\Controller\Costo;

use App\Repository\Costo\MarcaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MarcaController extends AbstractController
{
    /**
     * @Route("/api/marcas", methods={"GET"})
     */
    public function findAll(MarcaRepository $repository): JsonResponse
    {
        return new JsonResponse($repository->getAll());
    }

    /**
     * @Route("/api/marca", methods={"POST"})
     */
    public function post(Request $request, MarcaRepository $repository): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?: [];
        return $repository->post($data);
    }

    /**
     * @Route("/api/marca/{id}", methods={"PUT"})
     */
    public function put(int $id, Request $request, MarcaRepository $repository): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?: [];
        return $repository->updateItem($id, $data);
    }

    /**
     * @Route("/api/marca/{id}", methods={"DELETE"})
     */
    public function delete(int $id, MarcaRepository $repository): JsonResponse
    {
        return $repository->deleteItem($id);
    }
}
