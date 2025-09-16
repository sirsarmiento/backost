<?php

namespace App\Controller\Costo;

use App\Entity\Costo\Producto;
use App\Repository\Costo\ProductoRepository;

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

class ProductoController extends AbstractController
{
    /**
     * @Route("/api/producto", methods={"POST"})
     * @OA\Post(
     *     summary="Crear un nuevo producto",
     *     description="Crea un nuevo producto con sus datos básicos",
     *     operationId="createProducto",
     *     tags={"Productos"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos del producto",
     *         @OA\JsonContent(
     *             required={"nombre", "medida", "clasificacion"},
     *             @OA\Property(property="nombre", type="string", example="Producto", description="Nombre del producto"),
     *             @OA\Property(property="sku", type="string", example="sku", description="SKU del producto (opcional)"),
     *             @OA\Property(property="descripcion", type="string", example="Descrip", description="Descripción del producto"),
     *             @OA\Property(property="clasificacion", type="string", example="Proyecto", description="Clasificación del producto"),
     *             @OA\Property(property="medida", type="string", example="Metros", description="Unidad de medida del producto")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Producto creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Producto creado exitosamente"),
     *             @OA\Property(property="productoId", type="integer", example=1)
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
    public function post(Request $request,ValidatorInterface $validator,Helper $helper,ProductoRepository $repository): JsonResponse
    {   
        try {
            $data = json_decode($request->getContent(),true);
            return $repository->post($data,$validator,$helper); 
        } catch (Exception $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],500);
        }
    }

   /**
     * @Route("api/productos", methods={"GET"})
     * @OA\Get(
     *     summary="Obtener todos los productos",
     *     description="Retorna una lista de todos los productos",
     *     operationId="getAllProductos",
     *     tags={"Productos"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de productos obtenida exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Productos obtenidos exitosamente"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nombre", type="string", example="Producto"),
     *                     @OA\Property(property="sku", type="string", example="sku"),
     *                     @OA\Property(property="descripcion", type="string", example="Descrip"),
     *                     @OA\Property(property="clasificacion", type="string", example="Proyecto"),
     *                     @OA\Property(property="medida", type="string", example="Metros"),
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
     *             @OA\Property(property="message", type="string", example="Error al obtener los productos")
     *         )
     *     )
     * )
     * @Security(name="Bearer")
     */
    public function findAll(Request $request,ProductoRepository $repository): JsonResponse
    {
        $data = $repository->getAll();
        // Verifica qué datos estás obteniendo
        if (empty($data)) {
            return new JsonResponse([
                'message' => 'No se encontraron productos',
                'data' => []
            ], 200);
        }
        
        return new JsonResponse([
            'message' => 'Productos obtenidos exitosamente',
            'data' => $data,
            'count' => count($data)
        ], 200);
    }

    /**
     * @Route("/api/producto/{id}", methods={"PUT"})
     * @OA\Put(
     *     summary="Actualizar un producto existente",
     *     description="Actualiza los datos de un producto",
     *     operationId="updateProducto",
     *     tags={"Productos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del producto a actualizar",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos del producto a actualizar",
     *         @OA\JsonContent(
     *             required={"nombre", "medida", "clasificacion"},
     *             @OA\Property(property="nombre", type="string", example="Producto Actualizado", description="Nombre del producto"),
     *             @OA\Property(property="sku", type="string", example="sku-actualizado", description="SKU del producto"),
     *             @OA\Property(property="descripcion", type="string", example="Descripción actualizada", description="Descripción del producto"),
     *             @OA\Property(property="clasificacion", type="string", example="Proyecto", description="Clasificación del producto"),
     *             @OA\Property(property="medida", type="string", example="Kilos", description="Unidad de medida del producto")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Producto actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Producto actualizado exitosamente")
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
     *         description="Producto no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Producto no encontrado")
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
    public function put(int $id, Request $request, ValidatorInterface $validator, Helper $helper, ProductoRepository $repository): JsonResponse
    {   
        try {
            $data = json_decode($request->getContent(), true);
            return $repository->update($id, $data, $validator, $helper); 
        } catch (Exception $e) {
            return new JsonResponse(['msg' => 'Error del Servidor', 'error' => $e->getMessage()], 500);
        }
    }
}
