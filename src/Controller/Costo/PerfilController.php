<?php

namespace App\Controller\Costo;

use App\Entity\Costo\Perfil;
use App\Entity\Costo\Parametro;
use App\Repository\Costo\PerfilRepository;
use App\Repository\Costo\ParametroRepository;

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

class PerfilController extends AbstractController
{
    /**
     * @Route("/api/perfil", methods={"POST"})
     * @OA\Post(
     *     summary="Crear un nuevo perfil con sus parámetros",
     *     description="Crea un nuevo perfil empresarial con todos sus datos y parámetros asociados",
     *     operationId="createPerfil",
     *     tags={"Perfiles"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos del perfil y sus parámetros",
     *         @OA\JsonContent(
     *             required={"nombre", "tipo", "sector", "empleados", "rif", "periodo", "direccion", "moneda", "parametros"},
     *             @OA\Property(property="nombre", type="string", example="Mi Empresa", description="Nombre del perfil empresarial"),
     *             @OA\Property(property="tipo", type="string", example="Pyme", description="Tipo de empresa"),
     *             @OA\Property(property="sector", type="string", example="Manufactura", description="Sector económico"),
     *             @OA\Property(property="empleados", type="integer", example=25, description="Número de empleados"),
     *             @OA\Property(property="rif", type="string", example="J-123456789", description="RIF de la empresa"),
     *             @OA\Property(property="periodo", type="string", example="Mensual", description="Periodo de reporting"),
     *             @OA\Property(property="direccion", type="string", example="Av. Principal #123", description="Dirección de la empresa"),
     *             @OA\Property(property="moneda", type="string", example="Bs", description="Moneda utilizada"),
     *             @OA\Property(
     *                 property="parametros",
     *                 type="array",
     *                 description="Lista de parámetros del perfil",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"unidad", "tipo", "descripcion", "prodMaxHoras", "horasMax", "horasUso"},
     *                     @OA\Property(property="unidad", type="string", example="Metros", description="Unidad de medida"),
     *                     @OA\Property(property="tipo", type="string", example="Maquinaria", description="Tipo de parámetro"),
     *                     @OA\Property(property="descripcion", type="string", example="Máquina de producción", description="Descripción del parámetro"),
     *                     @OA\Property(property="prodMaxHoras", type="integer", example=100, description="Producción máxima por hora"),
     *                     @OA\Property(property="horasMax", type="integer", example=240, description="Horas máximas disponibles"),
     *                     @OA\Property(property="horasUso", type="integer", example=180, description="Horas de uso reales")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Perfil creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Perfil y parámetros creados exitosamente"),
     *             @OA\Property(property="perfilId", type="integer", example=1)
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
    public function post(Request $request,ValidatorInterface $validator,Helper $helper,PerfilRepository $repository): JsonResponse
    {   
        try {
            $data = json_decode($request->getContent(),true);
            return $repository->post($data,$validator,$helper); 
        } catch (Exception $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],500);
        }
    }

    /**
     * @Route("/api/perfil", methods={"GET"})
     * @OA\Get(
     *     summary="Obtener todos los perfils",
     *     description="Retorna una lista de todos los perfils con sus parámetros asociados",
     *     operationId="getAllPerfils",
     *     tags={"Perfiles"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de perfils obtenida exitosamente",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nombre", type="string", example="name"),
     *                 @OA\Property(property="tipo", type="string", example="Pyme"),
     *                 @OA\Property(property="sector", type="string", example="sectyore"),
     *                 @OA\Property(property="empleados", type="integer", example=2),
     *                 @OA\Property(property="rif", type="string", example="rif"),
     *                 @OA\Property(property="periodo", type="string", example="Mensual"),
     *                 @OA\Property(property="direccion", type="string", example="dire"),
     *                 @OA\Property(property="moneda", type="string", example="Bs"),
     *                 @OA\Property(
     *                     property="parametros",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="tipo", type="string", example="A"),
     *                         @OA\Property(property="descripcion", type="string", example="A"),
     *                         @OA\Property(property="unidad", type="string", example="Metros"),
     *                         @OA\Property(property="prodMaxHoras", type="integer", example=2),
     *                         @OA\Property(property="horasMax", type="integer", example=1),
     *                         @OA\Property(property="horasUso", type="integer", example=1)
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
     *             @OA\Property(property="message", type="string", example="Error al obtener los perfils")
     *         )
     *     )
     * )
     * @Security(name="Bearer")
     */
    public function findAll(Request $request,PerfilRepository $repository): JsonResponse
    {
        $data = $repository->getAllWithParametros();
        // Verifica qué datos estás obteniendo
        if (empty($data)) {
            return new JsonResponse([
                'message' => 'No se encontraron perfiles',
                'data' => []
            ], 200);
        }
        
        return new JsonResponse([
            'message' => 'Perfiles obtenidos exitosamente',
            'data' => $data,
            'count' => count($data)
        ], 200);
    }

    /**
     * @Route("/api/perfil/{id}", methods={"PUT"})
     * @OA\Put(
     *     summary="Actualizar un perfil existente con sus parámetros",
     *     description="Actualiza un perfil empresarial y sus parámetros asociados",
     *     operationId="updatePerfil",
     *     tags={"Perfiles"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del perfil a actualizar",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos del perfil y sus parámetros a actualizar",
     *         @OA\JsonContent(
     *             required={"nombre", "tipo", "sector", "empleados", "rif", "periodo", "direccion", "moneda", "parametros"},
     *             @OA\Property(property="nombre", type="string", example="Mi Empresa Actualizada", description="Nombre del perfil empresarial"),
     *             @OA\Property(property="tipo", type="string", example="Pyme", description="Tipo de empresa"),
     *             @OA\Property(property="sector", type="string", example="Manufactura", description="Sector económico"),
     *             @OA\Property(property="empleados", type="integer", example=30, description="Número de empleados"),
     *             @OA\Property(property="rif", type="string", example="J-123456789", description="RIF de la empresa"),
     *             @OA\Property(property="periodo", type="string", example="Mensual", description="Periodo de reporting"),
     *             @OA\Property(property="direccion", type="string", example="Av. Principal #456", description="Dirección de la empresa"),
     *             @OA\Property(property="moneda", type="string", example="Bs", description="Moneda utilizada"),
     *             @OA\Property(
     *                 property="parametros",
     *                 type="array",
     *                 description="Lista de parámetros del perfil",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"id", "unidad", "tipo", "descripcion", "prodMaxHoras", "horasMax", "horasUso"},
     *                     @OA\Property(property="id", type="integer", example=1, description="ID del parámetro (para actualizar) o null para crear nuevo"),
     *                     @OA\Property(property="unidad", type="string", example="Metros", description="Unidad de medida"),
     *                     @OA\Property(property="tipo", type="string", example="Maquinaria", description="Tipo de parámetro"),
     *                     @OA\Property(property="descripcion", type="string", example="Máquina de producción actualizada", description="Descripción del parámetro"),
     *                     @OA\Property(property="prodMaxHoras", type="integer", example=120, description="Producción máxima por hora"),
     *                     @OA\Property(property="horasMax", type="integer", example=250, description="Horas máximas disponibles"),
     *                     @OA\Property(property="horasUso", type="integer", example=200, description="Horas de uso reales")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Perfil actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Perfil y parámetros actualizados exitosamente"),
     *             @OA\Property(property="perfilId", type="integer", example=1)
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
     *         description="Perfil no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Perfil no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error de validación en los datos"),
     *             @OA\Property(property="errors", type="object", example={"nombre": "Este valor no debe estar vacío"})
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
    public function put(int $id, Request $request, ValidatorInterface $validator, Helper $helper, PerfilRepository $repository): JsonResponse
    {   
        try {
            $data = json_decode($request->getContent(), true);
            return $repository->update($id, $data, $validator, $helper); 
        } catch (Exception $e) {
            return new JsonResponse(['msg' => 'Error del Servidor', 'error' => $e->getMessage()], 500);
        }
    }
}
