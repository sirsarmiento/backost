<?php

namespace App\Controller\Costo;

use App\Repository\Costo\ProveedorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Service\Helper;

class ProveedorController extends AbstractController
{
    /**
     * @Route("/api/proveedor", methods={"POST"})
     */
    public function post(Request $request, ValidatorInterface $validator, Helper $helper, ProveedorRepository $repository): JsonResponse
    {
        return $repository->post(json_decode($request->getContent(), true) ?: [], $validator, $helper);
    }

    /**
     * @Route("/api/proveedores", methods={"GET"})
     */
    public function findAll(ProveedorRepository $repository): JsonResponse
    {
        $data = $repository->getAll();
        return new JsonResponse(['message' => 'Proveedores obtenidos', 'data' => $data, 'count' => count($data)]);
    }

    /**
     * @Route("/api/proveedor/{id}", methods={"PUT"})
     */
    public function put(int $id, Request $request, ValidatorInterface $validator, Helper $helper, ProveedorRepository $repository): JsonResponse
    {
        return $repository->update($id, json_decode($request->getContent(), true) ?: [], $validator, $helper);
    }
}
