<?php

namespace App\Controller\Costo;

use App\Entity\Costo\Activo;
use App\Repository\Costo\ActivoRepository;

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
use Symfony\Component\Validator\Constraints\Json;

class ActivoController extends AbstractController
{
    /**
     * @Route("api/activo", methods={"POST"})
     * @OA\Post(
     *     summary="Crear un nuevo activo",
     *     description="Crea un nuevo activo con sus datos básicos",
     *     operationId="createActivo",
     *     tags={"Activos"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos del activo",
     *         @OA\JsonContent(
     *             required={"nombre", "costoInicial", "valorResidual", "vidaUtil"},
     *             @OA\Property(property="nombre", type="string", example="Maquinaria Pesada", description="Nombre del activo"),
     *             @OA\Property(property="costoInicial", type="number", format="float", example=50000.00, description="Costo inicial del activo"),
     *             @OA\Property(property="valorResidual", type="number", format="float", example=5000.00, description="Valor residual del activo"),
     *             @OA\Property(property="vidaUtil", type="integer", example=5, description="Vida útil en años"),
     *             @OA\Property(property="fechaCompra", type="string", format="date", example="2023-01-15", description="Fecha de compra (opcional)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Activo creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Activo creado exitosamente"),
     *             @OA\Property(property="activoId", type="integer", example=1)
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
     *             @OA\Property(property="errors", type="string", example="costoInicial: Este valor debe ser mayor que 0")
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
    public function post(Request $request,ValidatorInterface $validator,Helper $helper,ActivoRepository $repository): JsonResponse
    {   
        try {
            $data = json_decode($request->getContent(),true);
            return $repository->post($data,$validator,$helper); 
        } catch (Exception $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],500);
        }
    }

    /**
     * @Route("api/activos", methods={"GET"})
     * @OA\Get(
     *     summary="Obtener todos los activos",
     *     description="Retorna una lista de todos los activos",
     *     operationId="getAllActivos",
     *     tags={"Activos"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de activos obtenida exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Activos obtenidos exitosamente"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nombre", type="string", example="Maquinaria Pesada"),
     *                     @OA\Property(property="costoInicial", type="number", format="float", example=50000.00),
     *                     @OA\Property(property="valorResidual", type="number", format="float", example=5000.00),
     *                     @OA\Property(property="vidaUtil", type="integer", example=5),
     *                     @OA\Property(property="fechaCompra", type="string", format="date", example="2023-01-15"),
     *                     @OA\Property(property="createAt", type="string", format="date-time", example="2023-12-20 10:30:00"),
     *                     @OA\Property(property="createBy", type="string", example="usuario")
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
     *             @OA\Property(property="message", type="string", example="Error al obtener los activos")
     *         )
     *     )
     * )
     * @Security(name="Bearer")
     */
    public function findAll(Request $request,ActivoRepository $repository): JsonResponse
    {
        $data = $repository->getAll();
        // Verifica qué datos estás obteniendo
        if (empty($data)) {
            return new JsonResponse([
                'message' => 'No se encontraron activos',
                'data' => []
            ], 200);
        }
        
        return new JsonResponse([
            'message' => 'Activos obtenidos exitosamente',
            'data' => $data,
            'count' => count($data)
        ], 200);
    }

    /**
     * @Route("api/activo/{id}", methods={"PUT"})
     * @OA\Put(
     *     summary="Actualizar un activo existente",
     *     description="Actualiza los datos de un activo",
     *     operationId="updateActivo",
     *     tags={"Activos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del activo a actualizar",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos del activo a actualizar",
     *         @OA\JsonContent(
     *             required={"nombre", "costoInicial", "valorResidual", "vidaUtil"},
     *             @OA\Property(property="nombre", type="string", example="Maquinaria Pesada Actualizada", description="Nombre del activo"),
     *             @OA\Property(property="costoInicial", type="number", format="float", example=55000.00, description="Costo inicial del activo"),
     *             @OA\Property(property="valorResidual", type="number", format="float", example=6000.00, description="Valor residual del activo"),
     *             @OA\Property(property="vidaUtil", type="integer", example=6, description="Vida útil en años"),
     *             @OA\Property(property="fechaCompra", type="string", format="date", example="2023-02-20", description="Fecha de compra")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Activo actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Activo actualizado exitosamente")
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
     *         description="Activo no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Activo no encontrado")
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
    public function put(int $id, Request $request, ValidatorInterface $validator, Helper $helper, ActivoRepository $repository): JsonResponse
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
