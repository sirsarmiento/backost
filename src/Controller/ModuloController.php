<?php

namespace App\Controller;
use App\Dto\RolOutPutDto;
use App\Dto\WidgetsMenuOutPutDto;

use App\Entity\Modulo;
use App\Form\ModuloType;
use App\Repository\ModuloRepository;
use App\Repository\ModuloRolRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\Helper;
use Symfony\Component\Validator\Constraints\Json;
use App\Dto\MenuOutPutDto;
use App\Dto\MenuGeneralOutPutDto;
use App\Dto\ModuloOutPutDto;
use App\Dto\AutorizacionesOutPutDto;
use App\Dto\ComponenteOutPutDto;
use App\Dto\TipoComponenteOutPutDto;

class ModuloController extends AbstractController
{

        /**
        * @Route("/api/modulo/pagined", methods={"POST"})
        * @OA\Post(
         * summary="Modulo pagined",
         * description="Modulo pagined",
         * operationId="Moduloall",
         * tags={"Modulos"},
         * @OA\RequestBody(
         *    required=true,
         *    description="parametro",
         *    @OA\JsonContent(
         *       required={"page"},
         *       @OA\Property(property="page", type="integer", format="integer", example="1"),
         *       @OA\Property(property="rowByPage", type="integer", format="integer", example="1"),
         *       @OA\Property(property="word", type="integer", format="integer", example="null"),
         *    ),
         * ),
         * @OA\Response(
         *    response=422,
         *    description="Wrong credentials response",
         *    @OA\JsonContent(
         *       @OA\Property(property="message", type="string", example="Sorry, wrong email address or password. Please try again")
         *        )
         *     )
         * )
    */    
    public function findAll(Request $request,ModuloRepository $repository): JsonResponse
    {
        $param = json_decode($request->getContent(),true);

        $data = $repository
        ->findAllPage($param);
        if (!$data) {
            return new JsonResponse(['msg'=>'No existen Registros'],404);  
        }   
         return new JsonResponse($data,200);  
    }


        /**
        * @Route("/api/modulo", methods={"POST"})
        * @OA\Post(
         * summary="Create Modulo",
         * description="Create Modulo",
         * operationId="createmodulo",
         * tags={"Modulos"},
         * @OA\RequestBody(
         *    required=true,
         *    description="Data Modulo",
         *    @OA\JsonContent(
         *       required={"nombre","descripcion"},
         *       @OA\Property(property="nombre", type="string", example="Administracion"),
         *       @OA\Property(property="descripcion", type="string", example="Este modulo es para Gestion"),
         *       @OA\Property(property="icono", type="string", example="home"),
         *       @OA\Property(property="tipoComponente", type="string", example="Menu"),
         *       @OA\Property(property="path", type="string", example="/admin"),
         *       @OA\Property(property="orden", type="integer", example="1"),
         *       @OA\Property(property="padre", type="integer", example="1"),
         *    ),
         * ),
         * @OA\Response(
         *    response=422,
         *    description="Wrong credentials response",
         *    @OA\JsonContent(
         *       @OA\Property(property="message", type="string", example="Sorry, wrong email address or password. Please try again")
         *        )
         *     )
         * )
    */
    public function post(Request $request,ValidatorInterface $validator,Helper $helper): JsonResponse
    {   
        try {
            $data = json_decode($request->getContent(),true);
            $repository = $this->getDoctrine()->getRepository(Modulo::class);
            return $repository->post($data,$validator,$helper); 
        } catch (Exception $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],500);
        }
    }

    /**
        * @Route("/api/modulo/{id}", methods={"PUT"})
        * @OA\Put(
         * summary="Put Modulo",
         * description="Update Modulo",
         * operationId="updateModulo",
         * tags={"Modulos"},
         * @OA\RequestBody(
         *    required=true,
         *    description="Data Modulo",
         *    @OA\JsonContent(
         *       required={"nombre","descripcion"},
         *       @OA\Property(property="nombre", type="string", example="Administracion"),
         *       @OA\Property(property="descripcion", type="string", example="Este modulo es para Gestion"),
         *       @OA\Property(property="icono", type="string", example="home"),
         *       @OA\Property(property="tipoComponente", type="string", example="Menu"),
         *       @OA\Property(property="path", type="string", example="/admin"),
         *       @OA\Property(property="orden", type="integer", example="1"),
         *       @OA\Property(property="status", type="integer", example="1"),
         *       @OA\Property(property="padre", type="integer", example="1"),
         *    ),
         * ),
         * @OA\Response(
         *    response=422,
         *    description="Wrong credentials response",
         *    @OA\JsonContent(
         *       @OA\Property(property="message", type="string", example="Sorry, wrong email address or password. Please try again")
         *        )
         *     )
         * )
    */
    public function put($id,Request $request,ValidatorInterface $validator,Helper $helper): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(),true);
            $em =$this->getDoctrine()->getManager();
            $repository = $this->getDoctrine()->getRepository(Modulo::class);
            return $repository->put($data,$id,$validator,$helper); 
        } catch (Exception $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],409);
        }
    }


/**
     *  Get an Menu. 
     * @Route("/api/modulo/menu/opciones", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Returns menu",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=MenuGeneralOutPutDto::class))
     *     )
     * )
     * @OA\Tag(name="Modulos")
     * @Security(name="Bearer")
     */
    public function menu(ModuloRepository $repository): JsonResponse
    {
        $data = $repository
        ->menu();
        if (!$data) {
            return new JsonResponse(['msg'=>'No existen Registros'],200);  
        }   
         return new JsonResponse($data,200);  
    }
/**
     *  Get an Component. 
     * @Route("/api/modulo/componente", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Returns component",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=ComponenteOutPutDto::class))
     *     )
     * )
     * @OA\Tag(name="Modulos")
     * @Security(name="Bearer")
     */
    public function componentes(ModuloRepository $repository): JsonResponse
    {
        $data = $repository
        ->componente();
        if (!$data) {
            return new JsonResponse(['msg'=>'No existen Registros'],200);  
        }   
         return new JsonResponse($data,200);  
    }

    /**
     *  Get an Tipo de Componentes. 
     * @Route("/api/modulo/tipo/componente", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Returns component",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=TipoComponenteOutPutDto::class))
     *     )
     * )
     * @OA\Tag(name="Modulos")
     * @Security(name="Bearer")
     */
    public function tipoComponentes(ModuloRepository $repository): JsonResponse
    {
        $data =new TipoComponenteOutPutDto();

        $data->tipoComponentes=array("Menu","Widget") ;
        if (!$data) {
            return new JsonResponse(['msg'=>'No existen Registros'],200);  
        }   
         return new JsonResponse($data,200);  
    }

    /**
     *  Get an Tipo de Componentes. 
     * @Route("/api/widgets/menu", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Returns component",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=WidgetsMenuOutPutDto::class))
     *     )
     * )
     * @OA\Tag(name="Modulos")
     * @Security(name="Bearer")
     */
    public function getWidgetsMenu(ModuloRepository $repository): JsonResponse
    {
        $data = $repository
        ->getWidgetsMenu();
        if (!$data) {
            return new JsonResponse(['msg'=>'No existen Registros'],200);  
        }   
         return new JsonResponse($data,200);  
    }

    


    
    /**
     *  Get an user. 
     * @Route("/api/modulo/{id}", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Returns module",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=ModuloOutPutDto::class))
     *     )
     * )
     * @OA\Tag(name="Modulos")
     * @Security(name="Bearer")
     */
    public function findById($id, ModuloRepository $repository): JsonResponse
    {
        $data = $repository
        ->findById($id);
        if (!$data) {
            return new JsonResponse(['msg'=>'No existen Registros'],200);  
        }   
         return new JsonResponse($data,200);  
    }


    
    /**
     *  Get an Autorizaciones. 
     * @Route("/api/modulo/autorizaciones/all", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Returns autorizaciones",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=AutorizacionesOutPutDto::class))
     *     )
     * )
     * @OA\Tag(name="Modulos")
     * @Security(name="Bearer")
     */
    public function autorizaciones(ModuloRepository $repository): JsonResponse
    {
        $data =new AutorizacionesOutPutDto();

        $data->autorizaciones=array("Incluir","Modificar","Eliminar","Consultar") ;
        if (!$data) {
            return new JsonResponse(['msg'=>'No existen Registros'],200);  
        }   
         return new JsonResponse($data,200);  
    }



    /**
        * @Route("/api/modulo/{id}", methods={"DELETE"})
        * @OA\Delete(
         * summary="Delete Modulo",
         * description="Delete Modulo",
         * operationId="deletemodulo",
         * tags={"Modulos"},
         * @OA\Response(
         *    response=422,
         *    description="Wrong credentials response",
         *    @OA\JsonContent(
         *       @OA\Property(property="message", type="string", example="Sorry, wrong email address or password. Please try again")
         *        )
         *     )
         * )
    */
    public function delete($id,ValidatorInterface $validator,Helper $helper): JsonResponse
    {
        try {
            $em =$this->getDoctrine()->getManager();
            $repository = $this->getDoctrine()->getRepository(Modulo::class);
            return $repository->delete($id,$validator,$helper); 
        } catch (Exception $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],500);
        }
    }





}
