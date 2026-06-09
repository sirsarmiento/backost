<?php

namespace App\Controller\Costo;

use App\Entity\Costo\Familia;
use App\Entity\Costo\SubFamilia;
use App\Repository\Costo\FamiliaRepository;
use App\Repository\Costo\SubFamiliaRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\Helper;
use Symfony\Component\Validator\Constraints\Json;

class FamiliaController extends AbstractController
{
    /**
     * @Route("/api/familia", methods={"POST"})
     * @OA\Post(
     *     summary="Crear una nueva familia con sus subfamilias",
     *     description="Crea una nueva familia empresarial con todas sus subfamilias asociadas",
     *     operationId="createFamilia",
     *     tags={"Familias"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos de la familia y sus subfamilias",
     *         @OA\JsonContent(
     *             required={"codigo", "nombre", "empresaId", "subFamilias"},
     *             @OA\Property(property="codigo", type="string", example="F001", description="Código de la familia"),
     *             @OA\Property(property="nombre", type="string", example="Materia Prima", description="Nombre de la familia"),
     *             @OA\Property(property="empresaId", type="integer", example=1, description="ID de la empresa"),
     *             @OA\Property(
     *                 property="subFamilias",
     *                 type="array",
     *                 description="Lista de subfamilias",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"codigo", "nombre"},
     *                     @OA\Property(property="codigo", type="string", example="SF001", description="Código de la subfamilia"),
     *                     @OA\Property(property="nombre", type="string", example="Plásticos", description="Nombre de la subfamilia")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Familia creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Familia y subfamilias creadas exitosamente"),
     *             @OA\Property(property="familiaId", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Datos de entrada inválidos",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Datos incompletos o inválidos"),
     *             @OA\Property(property="errors", type="object", example={"codigo": "Este campo es requerido"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error de validación en los datos"),
     *             @OA\Property(property="errors", type="string", example="codigo: Este valor no debe estar vacío")
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
    public function post(Request $request, ValidatorInterface $validator, Helper $helper, FamiliaRepository $repository): JsonResponse
    {   
        try {
            $data = json_decode($request->getContent(), true);
            return $repository->post($data, $validator, $helper); 
        } catch (\Exception $e) {
            return new JsonResponse(['msg'=>'Error del Servidor', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/api/familia", methods={"GET"})
     * @OA\Get(
     *     summary="Obtener todas las familias",
     *     description="Retorna una lista de todas las familias con sus subfamilias asociadas",
     *     operationId="getAllFamilias",
     *     tags={"Familias"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de familias obtenida exitosamente",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="codigo", type="string", example="F001"),
     *                 @OA\Property(property="nombre", type="string", example="Materia Prima"),
     *                 @OA\Property(property="empresaId", type="integer", example=1),
     *                 @OA\Property(property="createAt", type="string", format="datetime", example="2024-01-01 10:00:00"),
     *                 @OA\Property(property="createBy", type="string", example="admin"),
     *                 @OA\Property(property="updateAt", type="string", format="datetime", nullable=true),
     *                 @OA\Property(property="updateBy", type="string", nullable=true),
     *                 @OA\Property(
     *                     property="subFamilias",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="codigo", type="string", example="SF001"),
     *                         @OA\Property(property="nombre", type="string", example="Plásticos")
     *                     )
     *                 )
     *             )
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
     *             @OA\Property(property="message", type="string", example="Error al obtener las familias")
     *         )
     *     )
     * )
     * @Security(name="Bearer")
     */
    public function findAll(Request $request, FamiliaRepository $repository): JsonResponse
    {
        $data = $repository->getAllWithSubFamilias();
        
        if (empty($data)) {
            return new JsonResponse([
                'message' => 'No se encontraron familias',
                'data' => []
            ], 200);
        }
        
        return new JsonResponse([
            'message' => 'Familias obtenidas exitosamente',
            'data' => $data,
            'count' => count($data)
        ], 200);
    }

    /**
     * @Route("/api/familia/{id}", methods={"PUT"})
     * @OA\Put(
     *     summary="Actualizar una familia existente con sus subfamilias",
     *     description="Actualiza una familia empresarial y sus subfamilias asociadas",
     *     operationId="updateFamilia",
     *     tags={"Familias"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la familia a actualizar",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos de la familia y sus subfamilias a actualizar",
     *         @OA\JsonContent(
     *             required={"codigo", "nombre", "empresaId", "subFamilias"},
     *             @OA\Property(property="codigo", type="string", example="F001", description="Código de la familia"),
     *             @OA\Property(property="nombre", type="string", example="Materia Prima Actualizada", description="Nombre de la familia"),
     *             @OA\Property(property="empresaId", type="integer", example=1, description="ID de la empresa"),
     *             @OA\Property(
     *                 property="subFamilias",
     *                 type="array",
     *                 description="Lista de subfamilias",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"id", "codigo", "nombre"},
     *                     @OA\Property(property="id", type="integer", example=1, description="ID de la subfamilia (para actualizar) o null para crear nuevo"),
     *                     @OA\Property(property="codigo", type="string", example="SF001", description="Código de la subfamilia"),
     *                     @OA\Property(property="nombre", type="string", example="Plásticos Actualizado", description="Nombre de la subfamilia")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Familia actualizada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Familia y subfamilias actualizadas exitosamente"),
     *             @OA\Property(property="familiaId", type="integer", example=1)
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
     *         description="Familia no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Familia no encontrada")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error de validación en los datos"),
     *             @OA\Property(property="errors", type="object", example={"codigo": "Este valor no debe estar vacío"})
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
    public function put(int $id, Request $request, ValidatorInterface $validator, Helper $helper, FamiliaRepository $repository): JsonResponse
    {   
        try {
            $data = json_decode($request->getContent(), true);
            return $repository->update($id, $data, $validator, $helper); 
        } catch (\Exception $e) {
            return new JsonResponse(['msg' => 'Error del Servidor', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/api/familia/{id}", methods={"DELETE"})
     * @OA\Delete(
     *     summary="Eliminar una familia",
     *     description="Elimina una familia y todas sus subfamilias asociadas",
     *     operationId="deleteFamilia",
     *     tags={"Familias"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la familia a eliminar",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Familia eliminada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Familia y subfamilias eliminadas exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Familia no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Familia no encontrada")
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
    public function delete(int $id, FamiliaRepository $repository): JsonResponse
    {
        try {
            return $repository->delete($id);
        } catch (\Exception $e) {
            return new JsonResponse(['msg' => 'Error del Servidor', 'error' => $e->getMessage()], 500);
        }
    }
}
