<?php

namespace App\Controller\Costo;

use App\Entity\Costo\Costo;
use App\Repository\Costo\CostoRepository;
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

class CostoController extends AbstractController
{
    /**
     * @Route("/api/costo", methods={"POST"})
     * @OA\Post(
     *     summary="Crear un nuevo costo",
     *     description="Crea un nuevo costo con sus datos básicos",
     *     operationId="createcosto",
     *     tags={"costos"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos del costo",
     *         @OA\JsonContent(
     *             required={"tipo", "concepto", "precio", "clasificacion"},
     *             @OA\Property(property="tipo", type="string", example="Servicio", description="Tipo de costo"),
     *             @OA\Property(property="concepto", type="string", example="Consultoría IT", description="Concepto del costo"),
     *             @OA\Property(property="precio", type="number", format="float", example=199.99, description="Precio del costo"),
     *             @OA\Property(property="clasificacion", type="string", example="Premium", description="Clasificación del costo"),
     *             @OA\Property(property="producto", type="integer", example=1, description="ID del costo padre (opcional)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="costo creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="costo creado exitosamente"),
     *             @OA\Property(property="costoId", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Datos de entrada inválidos",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Datos incompletos o inválidos"),
     *             @OA\Property(property="errors", type="object", example={"tipo": "Este campo es requerido"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error de validación en los datos"),
     *             @OA\Property(property="errors", type="string", example="tipo: Este valor no debe estar vacío")
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
    public function post(Request $request,ValidatorInterface $validator,Helper $helper,CostoRepository $repository): JsonResponse
    {   
        try {
            $data = json_decode($request->getContent(),true);
            return $repository->post($data,$validator,$helper); 
        } catch (Exception $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],500);
        }
    }

    /**
     * @Route("/api/costos", methods={"GET"})
     * @OA\Get(
     *     summary="Obtener todos los costos",
     *     description="Retorna una lista de todos los costos",
     *     operationId="getAllcostos",
     *     tags={"costos"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de costos obtenida exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="costos obtenidos exitosamente"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="tipo", type="string", example="Servicio"),
     *                     @OA\Property(property="concepto", type="string", example="Consultoría IT"),
     *                     @OA\Property(property="precio", type="number", format="float", example=199.99),
     *                     @OA\Property(property="clasificacion", type="string", example="Premium"),
     *                     @OA\Property(property="producto", type="integer", example=1, description="ID del costo padre"),
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
     *             @OA\Property(property="message", type="string", example="Error al obtener los costos")
     *         )
     *     )
     * )
     * @Security(name="Bearer")
     */
    public function findAll(Request $request, CostoRepository $repository): JsonResponse
    {
        try {
            $data = $repository->getAll();
            
            if (empty($data)) {
                return new JsonResponse([
                    'success' => true,
                    'message' => 'No se encontraron costos',
                    'data' => [],
                    'count' => 0
                ], 200);
            }
            
            return new JsonResponse([
                'success' => true,
                'message' => 'costos obtenidos exitosamente',
                'data' => $data,
                'count' => count($data)
            ], 200);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error al obtener los costos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @Route("/api/costo/{id}", methods={"PUT"})
     * @OA\Put(
     *     summary="Actualizar un costo existente",
     *     description="Actualiza los datos de un costo",
     *     operationId="updatecosto",
     *     tags={"costos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del costo a actualizar",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos del costo a actualizar",
     *         @OA\JsonContent(
     *             required={"tipo", "concepto", "precio", "clasificacion"},
     *             @OA\Property(property="tipo", type="string", example="Servicio Actualizado", description="Tipo de costo"),
     *             @OA\Property(property="concepto", type="string", example="Consultoría IT Avanzada", description="Concepto del costo"),
     *             @OA\Property(property="precio", type="number", format="float", example=299.99, description="Precio del costo"),
     *             @OA\Property(property="clasificacion", type="string", example="Enterprise", description="Clasificación del costo"),
     *             @OA\Property(property="producto", type="integer", example=2, description="ID del costo padre (opcional)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="costo actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="costo actualizado exitosamente")
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
     *         description="costo no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="costo no encontrado")
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
    public function put(int $id, Request $request, ValidatorInterface $validator, Helper $helper, CostoRepository $repository): JsonResponse
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

    /**
     * @Route("/api/costo/{id}", methods={"DELETE"})
     * @OA\Delete(
     *     summary="Eliminar un costo",
     *     description="Elimina un costo existente",
     *     operationId="deletecosto",
     *     tags={"costos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del costo a eliminar",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="costo eliminado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="costo eliminado exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="costo no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="costo no encontrado")
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
    public function delete(int $id, CostoRepository $repository): JsonResponse
    {   
        try {
            return $repository->delete($id); 
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error del Servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
