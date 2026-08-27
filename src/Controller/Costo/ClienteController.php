<?php

namespace App\Controller\Costo;

use App\Entity\Costo\Cliente;
use App\Repository\Costo\ClienteRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\Helper;

class ClienteController extends AbstractController
{
    /**
     * @Route("/api/cliente", methods={"POST"})
     * @OA\Post(
     *     summary="Crear un nuevo cliente",
     *     description="Crea un nuevo cliente con sus datos básicos",
     *     operationId="createCliente",
     *     tags={"Clientes"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos del cliente",
     *         @OA\JsonContent(
     *             required={"nombre", "apellido"},
     *             @OA\Property(property="nombre", type="string", example="Juan", description="Nombre del cliente"),
     *             @OA\Property(property="apellido", type="string", example="Pérez", description="Apellido del cliente"),
     *             @OA\Property(property="email", type="string", example="juan.perez@example.com", description="Email del cliente"),
     *             @OA\Property(property="telefono", type="string", example="+123456789", description="Teléfono del cliente"),
     *             @OA\Property(property="direccion", type="string", example="Av. Principal 123", description="Dirección del cliente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Cliente creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Cliente creado exitosamente"),
     *             @OA\Property(property="clienteId", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Datos de entrada inválidos",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Datos incompletos o inválidos"),
     *             @OA\Property(property="errors", type="object", example={"nombre": "Este campo es requerido"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error de validación en los datos"),
     *             @OA\Property(property="errors", type="string", example="nombre: Este valor no debe estar vacío")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error interno del servidor")
     *         )
     *     )
     * )
     */
    public function post(Request $request, ValidatorInterface $validator, Helper $helper, ClienteRepository $repository): JsonResponse
    {   
        try {
            $data = json_decode($request->getContent(), true);
            return $repository->post($data, $validator, $helper); 
        } catch (\Exception $e) {
            return new JsonResponse(['msg' => 'Error del Servidor'], 500);
        }
    }

    /**
     * @Route("/api/clientes", methods={"GET"})
     * @OA\Get(
     *     summary="Obtener todos los clientes",
     *     description="Retorna una lista de todos los clientes",
     *     operationId="getAllClientes",
     *     tags={"Clientes"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de clientes obtenida exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Clientes obtenidos exitosamente"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nombre", type="string", example="Juan"),
     *                     @OA\Property(property="apellido", type="string", example="Pérez"),
     *                     @OA\Property(property="email", type="string", example="juan.perez@example.com"),
     *                     @OA\Property(property="telefono", type="string", example="+123456789"),
     *                     @OA\Property(property="direccion", type="string", example="Av. Principal 123")
     *                 )
     *             ),
     *             @OA\Property(property="count", type="integer", example=5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Token de acceso no válido")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error al obtener los clientes")
     *         )
     *     )
     * )
     * @Security(name="Bearer")
     */
    public function findAll(Request $request, ClienteRepository $repository): JsonResponse
    {
        $data = $repository->getAll();
        if (empty($data)) {
            return new JsonResponse([
                'message' => 'No se encontraron clientes',
                'data' => []
            ], 200);
        }
        
        return new JsonResponse([
            'message' => 'Clientes obtenidos exitosamente',
            'data' => $data,
            'count' => count($data)
        ], 200);
    }

    /**
     * @Route("/api/cliente/{id}", methods={"PUT"})
     * @OA\Put(
     *     summary="Actualizar un cliente existente",
     *     description="Actualiza los datos de un cliente",
     *     operationId="updateCliente",
     *     tags={"Clientes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del cliente a actualizar",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos del cliente a actualizar",
     *         @OA\JsonContent(
     *             required={"nombre", "apellido"},
     *             @OA\Property(property="nombre", type="string", example="Juan Carlos", description="Nombre del cliente"),
     *             @OA\Property(property="apellido", type="string", example="Pérez Gómez", description="Apellido del cliente"),
     *             @OA\Property(property="email", type="string", example="juan.carlos@example.com", description="Email del cliente"),
     *             @OA\Property(property="telefono", type="string", example="+987654321", description="Teléfono del cliente"),
     *             @OA\Property(property="direccion", type="string", example="Av. Principal 456", description="Dirección del cliente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cliente actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Cliente actualizado exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Datos de entrada inválidos",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Datos incompletos o inválidos")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Cliente no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Cliente no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error de validación en los datos")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error interno del servidor")
     *         )
     *     )
     * )
     */
    public function put(int $id, Request $request, ValidatorInterface $validator, Helper $helper, ClienteRepository $repository): JsonResponse
    {   
        try {
            $data = json_decode($request->getContent(), true);
            return $repository->update($id, $data, $validator, $helper); 
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error del Servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
