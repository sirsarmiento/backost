<?php

namespace App\Controller\Costo;

use App\Repository\Costo\TecnologiaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TecnologiaController extends AbstractController
{
    /**
     * @Route("/api/tecnologias", methods={"GET"})
     */
    public function findAll(TecnologiaRepository $repository): JsonResponse
    {
        return new JsonResponse($repository->getAll());
    }

    /**
     * @Route("/api/tecnologia", methods={"POST"})
     */
    public function post(Request $request, TecnologiaRepository $repository): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?: [];
        return $repository->post($data);
    }

    /**
     * @Route("/api/tecnologia/{id}", methods={"PUT"})
     */
    public function put(int $id, Request $request, TecnologiaRepository $repository): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?: [];
        return $repository->updateItem($id, $data);
    }

    /**
     * @Route("/api/tecnologia/{id}", methods={"DELETE"})
     */
    public function delete(int $id, TecnologiaRepository $repository): JsonResponse
    {
        return $repository->deleteItem($id);
    }
}
