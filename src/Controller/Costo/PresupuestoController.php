<?php

namespace App\Controller\Costo;

use App\Entity\Costo\Presupuesto;
use App\Repository\Costo\PresupuestoRepository;
use App\Entity\Costo\Piezas;
use App\Repository\Costo\PiezasRepository;

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


class PresupuestoController extends AbstractController
{
    /**
     * @Route("/api/presupuesto", methods={"POST"})
     * @OA\Post(
     *     summary="Crear un nuevo presupuesto con sus piezas",
     *     description="Crea un nuevo presupuesto con todos sus datos y piezas asociadas",
     *     operationId="createpresupuesto",
     *     tags={"Presupuestos"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos del presupuesto y sus piezas",
     *         @OA\JsonContent(
     *             required={"clasificacion", "descripcion", "numero", "fecha", "piezas"},
     *             @OA\Property(property="clasificacion", type="string", example="Proyecto", description="Clasificación del presupuesto"),
     *             @OA\Property(property="descripcion", type="string", example="Prototipo de ..", description="Descripción del presupuesto"),
     *             @OA\Property(property="numero", type="string", example="12345678", description="Número identificador"),
     *             @OA\Property(property="fecha", type="string", format="date-time", example="2025-10-03T04:00:00.000Z", description="Fecha del presupuesto"),
     *             @OA\Property(property="costoOperador", type="number", format="float", example=25.50, description="Costo por hora del operador"),
     *             @OA\Property(property="costoMaquina", type="number", format="float", example=15.75, description="Costo por hora de la máquina"),
     *             @OA\Property(property="tasaFalloGlobal", type="number", format="float", example=0.05, description="Tasa de fallo global del proceso"),
     *             @OA\Property(property="tiempoSetup", type="integer", example=30, description="Tiempo de setup en minutos"),
     *             @OA\Property(property="margenGanancia", type="number", format="float", example=20.00, description="Margen de ganancia en porcentaje"),
     *             @OA\Property(property="tiempoPostProcesado", type="integer", example=15, description="Tiempo de post-procesado en minutos"),
     *             @OA\Property(property="cantidadGlobal", type="integer", example=100, description="Cantidad global del presupuesto"),
     *             @OA\Property(property="delivery", type="number", format="float", example=50.00, description="Costo de delivery"),
     *             @OA\Property(property="cliente", type="integer", example=1, description="ID del cliente asociado"),
     *             @OA\Property(property="producto", type="integer", example=1, description="ID del producto asociado"),
     *             @OA\Property(
     *                 property="piezas",
     *                 type="array",
     *                 description="Lista de piezas del presupuesto",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"nombre", "gramos", "metros", "horas", "minutos", "precioMaterial"},
     *                     @OA\Property(property="id", type="integer", example=1, description="ID de la pieza (opcional para creación)"),
     *                     @OA\Property(property="nombre", type="string", example="PIEZA 1", description="Nombre de la pieza"),
     *                     @OA\Property(property="gramos", type="number", format="float", example=1.0, description="Peso en gramos"),
     *                     @OA\Property(property="metros", type="number", format="float", example=2.0, description="Longitud en metros"),
     *                     @OA\Property(property="horas", type="integer", example=3, description="Horas de producción"),
     *                     @OA\Property(property="minutos", type="integer", example=2, description="Minutos de producción"),
     *                     @OA\Property(property="precioMaterial", type="number", format="float", example=10.50, description="Precio del material"),
     *                     @OA\Property(property="tipo", type="string", example="Producción", description="Tipo de pieza", nullable=true),
     *                     @OA\Property(property="cantidad", type="integer", example=5, description="Cantidad de piezas"),
     *                     @OA\Property(property="producto", type="integer", example=1, description="ID del producto asociado"),
     *                     @OA\Property(property="activo", type="integer", example=1, description="ID del activo asociado"),
     *                     @OA\Property(property="maquina", type="integer", example=1, description="ID de la máquina asociada")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="presupuesto creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="presupuesto y piezas creados exitosamente"),
     *             @OA\Property(property="presupuestoId", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Datos de entrada inválidos",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Datos incompletos o inválidos"),
     *             @OA\Property(property="errors", type="object", example={"clasificacion": "Este campo es requerido"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error de validación en los datos"),
     *             @OA\Property(property="errors", type="string", example="clasificacion: Este valor no debe estar vacío")
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
    public function post(Request $request, ValidatorInterface $validator, Helper $helper, PresupuestoRepository $repository): JsonResponse
    {   
       try {
            $data = json_decode($request->getContent(),true);
            return $repository->post($data,$validator,$helper); 
        } catch (Exception $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],500);
        }
    }

    /**
     * @Route("/api/presupuestos", methods={"GET"})
     * @OA\Get(
     *     summary="Obtener todos los presupuestos",
     *     description="Retorna una lista de todos los presupuestos con sus parámetros asociados",
     *     operationId="getAllPresupuestos",
     *     tags={"Presupuestos"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de presupuestos obtenida exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Presupuesto obtenidos exitosamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="clasificacion", type="string", example="Proyecto"),
     *                     @OA\Property(property="descripcion", type="string", example="Prototipo de .."),
     *                     @OA\Property(property="numero", type="string", example="12345678"),
     *                     @OA\Property(property="fecha", type="string", format="date", example="2025-10-03"),
     *                     @OA\Property(property="costoOperador", type="number", format="float", example=25.50),
     *                     @OA\Property(property="costoMaquina", type="number", format="float", example=15.75),
     *                     @OA\Property(property="tasaFalloGlobal", type="number", format="float", example=0.05),
     *                     @OA\Property(property="tiempoSetup", type="integer", example=30),
     *                     @OA\Property(property="margenGanancia", type="number", format="float", example=20.00),
     *                     @OA\Property(property="tiempoPostProcesado", type="integer", example=15),
     *                     @OA\Property(property="cantidadGlobal", type="integer", example=100),
     *                     @OA\Property(property="delivery", type="number", format="float", example=50.00),
     *                     @OA\Property(property="cliente", type="integer", example=1),
     *                     @OA\Property(property="producto", type="integer", example=1),
     *                     @OA\Property(    
     *                         property="piezas",
     *                         type="array",
     *                         description="Lista de piezas del presupuesto",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="nombre", type="string", example="PIEZA 1"),
     *                             @OA\Property(property="gramos", type="number", format="float", example=1.0),
     *                             @OA\Property(property="metros", type="number", format="float", example=2.0),
     *                             @OA\Property(property="horas", type="integer", example=3),
     *                             @OA\Property(property="minutos", type="integer", example=2),
     *                             @OA\Property(property="precioMaterial", type="number", format="float", example=10.50),
     *                             @OA\Property(property="tipo", type="string", example="Producción"),
     *                             @OA\Property(property="cantidad", type="integer", example=5),
     *                             @OA\Property(property="producto", type="integer", example=1),
     *                             @OA\Property(property="activo", type="integer", example=1),
     *                             @OA\Property(property="maquina", type="integer", example=1)
     *                         )
     *                     )
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
     *             @OA\Property(property="message", type="string", example="Error al obtener los presupuestos")
     *         )
     *     )
     * )
     * @Security(name="Bearer")
     */
    public function findAll(Request $request,PresupuestoRepository $repository): JsonResponse
    {
        $data = $repository->getAllWithParts();
        // Verifica qué datos estás obteniendo
        if (empty($data)) {
            return new JsonResponse([
                'message' => 'No se encontraron presupuesto',
                'data' => []
            ], 200);
        }
        
        return new JsonResponse([
            'message' => 'Presupuesto obtenidos exitosamente',
            'data' => $data,
            'count' => count($data)
        ], 200);
    }

    /**
     * @Route("/api/presupuesto/{id}", methods={"PUT"})
     * @OA\Put(
     *     summary="Actualizar un presupuesto existente con sus parámetros",
     *     description="Actualiza un presupuesto empresarial y sus parámetros asociados",
     *     operationId="updatePresupuesto",
     *     tags={"Presupuestos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del presupuesto a actualizar",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos del presupuesto y sus piezas",
     *         @OA\JsonContent(
     *             required={"clasificacion", "descripcion", "numero", "fecha", "piezas"},
     *             @OA\Property(property="clasificacion", type="string", example="Proyecto", description="Clasificación del presupuesto"),
     *             @OA\Property(property="descripcion", type="string", example="Prototipo de ..", description="Descripción del presupuesto"),
     *             @OA\Property(property="numero", type="string", example="12345678", description="Número identificador"),
     *             @OA\Property(property="fecha", type="string", format="date-time", example="2025-10-03T04:00:00.000Z", description="Fecha del presupuesto"),
     *             @OA\Property(property="costoOperador", type="number", format="float", example=25.50, description="Costo por hora del operador"),
     *             @OA\Property(property="costoMaquina", type="number", format="float", example=15.75, description="Costo por hora de la máquina"),
     *             @OA\Property(property="tasaFalloGlobal", type="number", format="float", example=0.05, description="Tasa de fallo global del proceso"),
     *             @OA\Property(property="tiempoSetup", type="integer", example=30, description="Tiempo de setup en minutos"),
     *             @OA\Property(property="margenGanancia", type="number", format="float", example=20.00, description="Margen de ganancia en porcentaje"),
     *             @OA\Property(property="tiempoPostProcesado", type="integer", example=15, description="Tiempo de post-procesado en minutos"),
     *             @OA\Property(property="cantidadGlobal", type="integer", example=100, description="Cantidad global del presupuesto"),
     *             @OA\Property(property="delivery", type="number", format="float", example=50.00, description="Costo de delivery"),
     *             @OA\Property(property="cliente", type="integer", example=1, description="ID del cliente asociado"),
     *             @OA\Property(property="producto", type="integer", example=1, description="ID del producto asociado"),
     *             @OA\Property(
     *                 property="piezas",
     *                 type="array",
     *                 description="Lista de piezas del presupuesto",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"nombre", "gramos", "metros", "horas", "minutos", "precioMaterial"},
     *                     @OA\Property(property="id", type="integer", example=1, description="ID de la pieza (opcional para creación)"),
     *                     @OA\Property(property="nombre", type="string", example="PIEZA 1", description="Nombre de la pieza"),
     *                     @OA\Property(property="gramos", type="number", format="float", example=1.0, description="Peso en gramos"),
     *                     @OA\Property(property="metros", type="number", format="float", example=2.0, description="Longitud en metros"),
     *                     @OA\Property(property="horas", type="integer", example=3, description="Horas de producción"),
     *                     @OA\Property(property="minutos", type="integer", example=2, description="Minutos de producción"),
     *                     @OA\Property(property="precioMaterial", type="number", format="float", example=10.50, description="Precio del material"),
     *                     @OA\Property(property="tipo", type="string", example="Producción", description="Tipo de pieza", nullable=true),
     *                     @OA\Property(property="cantidad", type="integer", example=5, description="Cantidad de piezas"),
     *                     @OA\Property(property="producto", type="integer", example=1, description="ID del producto asociado"),
     *                     @OA\Property(property="activo", type="integer", example=1, description="ID del activo asociado"),
     *                     @OA\Property(property="maquina", type="integer", example=1, description="ID de la máquina asociada")
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
     *             @OA\Property(property="presupuestoId", type="integer", example=1)
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
    public function put(int $id, Request $request, ValidatorInterface $validator, Helper $helper, PresupuestoRepository $repository): JsonResponse
    {   
        try {
            $data = json_decode($request->getContent(), true);
            return $repository->update($id, $data, $validator, $helper); 
        } catch (Exception $e) {
            return new JsonResponse(['msg' => 'Error del Servidor', 'error' => $e->getMessage()], 500);
        }
    }
}