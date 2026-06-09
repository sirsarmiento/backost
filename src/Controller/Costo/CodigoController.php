<?php

namespace App\Controller\Costo;

use App\Entity\Costo\Codigo;
use App\Repository\Costo\CodigoRepository;

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

class CodigoController extends AbstractController
{
       /**
     * @Route("api/codigo", methods={"POST"})
     * @OA\Post(
     *     summary="Crear un nuevo código",
     *     description="Crea un nuevo código con sus datos básicos",
     *     operationId="createCodigo",
     *     tags={"Códigos"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos del código",
     *         @OA\JsonContent(
     *             required={"categoria", "tecnologia", "material", "codigo", "catalogo"},
     *             @OA\Property(property="categoria", type="string", example="Electrónica", description="Categoría del código"),
     *             @OA\Property(property="producto", type="integer", example=1, description="ID del producto asociado"),
     *             @OA\Property(property="tecnologia", type="string", example="SMD", description="Tecnología"),
     *             @OA\Property(property="familia", type="integer", example=1, description="ID de la familia asociada"),
     *             @OA\Property(property="subfamilia", type="integer", example=1, description="ID de la subfamilia asociada"),
     *             @OA\Property(property="material", type="string", example="Cobre", description="Material"),
     *             @OA\Property(property="codigo", type="string", example="ELEC-SMD-001", description="Código"),
     *             @OA\Property(property="catalogo", type="string", example="CAT-2024", description="Catálogo")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Código creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Código creado exitosamente"),
     *             @OA\Property(property="codigoId", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Datos de entrada inválidos",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Datos incompletos o inválidos"),
     *             @OA\Property(property="errors", type="object", example={"categoria": "Este campo es requerido"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error de validación en los datos"),
     *             @OA\Property(property="errors", type="string", example="categoria: Este valor no puede estar vacío")
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
    public function post(Request $request, ValidatorInterface $validator, Helper $helper, CodigoRepository $repository): JsonResponse
    {   
        try {
            $data = json_decode($request->getContent(), true);
            return $repository->post($data, $validator, $helper); 
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'msg' => 'Error del Servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @Route("api/codigos", methods={"GET"})
     * @OA\Get(
     *     summary="Obtener todos los códigos",
     *     description="Retorna una lista de todos los códigos",
     *     operationId="getAllCodigos",
     *     tags={"Códigos"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de códigos obtenida exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Códigos obtenidos exitosamente"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="categoria", type="string", example="Electrónica"),
     *                     @OA\Property(property="producto", type="object", 
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="nombre", type="string", example="Producto Ejemplo")
     *                     ),
     *                     @OA\Property(property="tecnologia", type="string", example="SMD"),
     *                     @OA\Property(property="familia", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="nombre", type="string", example="Familia Ejemplo")
     *                     ),
     *                     @OA\Property(property="subfamilia", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="nombre", type="string", example="Subfamilia Ejemplo")
     *                     ),
     *                     @OA\Property(property="material", type="string", example="Cobre"),
     *                     @OA\Property(property="codigo", type="string", example="ELEC-SMD-001"),
     *                     @OA\Property(property="catalogo", type="string", example="CAT-2024"),
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
     *             @OA\Property(property="message", type="string", example="Error al obtener los códigos")
     *         )
     *     )
     * )
     * @Security(name="Bearer")
     */
    public function findAll(Request $request, CodigoRepository $repository): JsonResponse
    {
        $data = $repository->getAll();
        
        if (empty($data)) {
            return new JsonResponse([
                'message' => 'No se encontraron códigos',
                'data' => []
            ], 200);
        }
        
        return new JsonResponse([
            'message' => 'Códigos obtenidos exitosamente',
            'data' => $data,
            'count' => count($data)
        ], 200);
    }

    /**
     * @Route("api/codigo/{id}", methods={"GET"})
     * @OA\Get(
     *     summary="Obtener un código por ID",
     *     description="Retorna un código específico por su ID",
     *     operationId="getCodigoById",
     *     tags={"Códigos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del código",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Código encontrado exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="categoria", type="string", example="Electrónica"),
     *                 @OA\Property(property="tecnologia", type="string", example="SMD"),
     *                 @OA\Property(property="material", type="string", example="Cobre"),
     *                 @OA\Property(property="codigo", type="string", example="ELEC-SMD-001"),
     *                 @OA\Property(property="catalogo", type="string", example="CAT-2024")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Código no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Código no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error al obtener el código")
     *         )
     *     )
     * )
     * @Security(name="Bearer")
     */
    public function findById(int $id, CodigoRepository $repository): JsonResponse
    {
        $data = $repository->getById($id);
        
        if (!$data) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Código no encontrado'
            ], 404);
        }
        
        return new JsonResponse([
            'success' => true,
            'data' => $data
        ], 200);
    }

    /**
     * @Route("api/codigo/{id}", methods={"PUT"})
     * @OA\Put(
     *     summary="Actualizar un código existente",
     *     description="Actualiza los datos de un código",
     *     operationId="updateCodigo",
     *     tags={"Códigos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del código a actualizar",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos del código a actualizar",
     *         @OA\JsonContent(
     *             required={"categoria", "tecnologia", "material", "codigo", "catalogo"},
     *             @OA\Property(property="categoria", type="string", example="Electrónica Actualizada", description="Categoría del código"),
     *             @OA\Property(property="producto", type="integer", example=2, description="ID del producto asociado"),
     *             @OA\Property(property="tecnologia", type="string", example="THT", description="Tecnología"),
     *             @OA\Property(property="familia", type="integer", example=2, description="ID de la familia asociada"),
     *             @OA\Property(property="subfamilia", type="integer", example=2, description="ID de la subfamilia asociada"),
     *             @OA\Property(property="material", type="string", example="Aluminio", description="Material"),
     *             @OA\Property(property="codigo", type="string", example="ELEC-THT-002", description="Código"),
     *             @OA\Property(property="catalogo", type="string", example="CAT-2025", description="Catálogo")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Código actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Código actualizado exitosamente")
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
     *         description="Código no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Código no encontrado")
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
    public function put(int $id, Request $request, ValidatorInterface $validator, Helper $helper, CodigoRepository $repository): JsonResponse
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
     * @Route("api/codigo/{id}", methods={"DELETE"})
     * @OA\Delete(
     *     summary="Eliminar un código",
     *     description="Elimina un código por su ID",
     *     operationId="deleteCodigo",
     *     tags={"Códigos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del código a eliminar",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Código eliminado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Código eliminado exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Código no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Código no encontrado")
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
    public function delete(int $id, CodigoRepository $repository): JsonResponse
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
