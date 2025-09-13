<?php

namespace App\Controller;
use App\Entity\Rol;
use App\Dto\RolOutPutDto;


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

use App\Repository\RolRepository;

class RolController extends AbstractController
{

        /**
        * @Route("/api/rol/pagined", methods={"POST"})
        * @OA\Post(
         * summary="Rol Pagined",
         * description="Rol Pagined",
         * operationId="RolAll",
         * tags={"Roles"},
         * @OA\RequestBody(
         *    required=true,
         *    description="parametro",
         *    @OA\JsonContent(
         *       required={"page"},
         *       @OA\Property(property="page", type="integer", format="integer", example="1"),
         *       @OA\Property(property="rowByPage", type="integer", format="integer", example="1"),
         *       @OA\Property(property="word", type="integer", format="integer", example="Dempre"),
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
    public function findAll(Request $request,RolRepository $repository): JsonResponse
    {
        $param = json_decode($request->getContent(),true);

        $data = $repository
        ->findAllPage($param);
         return $data;  
    }


    /**
     * Get list rol. 
     * @Route("/api/rol/list", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Returns Rol",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=RolOutPutDto::class))
     *     )
     * )
     * @OA\Tag(name="Roles")
     * @Security(name="Bearer")
     */
    public function findList(Request $request,RolRepository $repository): JsonResponse
    {
        $data = $repository
        ->findList();
        if (!$data) {
            return new JsonResponse(['msg'=>'No existen Registros'],200);  
        }   
         //return new JsonResponse($data,200);  
         return new JsonResponse($data,200);  
    }

    
        /**
        * @Route("/api/rol", methods={"POST"})
        * @OA\Post(
         * summary="Create Rol",
         * description="Create Rol",
         * operationId="createrol",
         * tags={"Roles"},
         * @OA\RequestBody(
         *    required=true,
         *    description="Data Role",
         *    @OA\JsonContent(
         *       required={"descripcion"},
         *       @OA\Property(property="descripcion", type="string", example="ROLE_PRUEBA"),
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
            $repository = $this->getDoctrine()->getRepository(Rol::class);
            return $repository->post($data,$validator,$helper); 
        } catch (Exception $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],500);
        }
    }

    /**
        * @Route("/api/rol/{id}", methods={"PUT"})
        * @OA\Put(
         * summary="Rol Modulo",
         * description="Rol Modulo",
         * operationId="updateRol",
         * tags={"Roles"},
         * @OA\RequestBody(
         *    required=true,
         *    description="Data Rol",
         *    @OA\JsonContent(
         *       required={"nombre","descripcion"},
         *       @OA\Property(property="descripcion", type="string", example="ROLE_PRUEBA"),
         *       @OA\Property(property="statusId", type="string", example="1"),
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
            $repository = $this->getDoctrine()->getRepository(Rol::class);
            return $repository->put($data,$id,$validator,$helper); 
        } catch (Exception $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],500);
        }
    }



    /**
        * @Route("/api/rol/{id}", methods={"DELETE"})
        * @OA\Delete(
         * summary="Delete Rol",
         * description="Delete Rol",
         * operationId="deleterol",
         * tags={"Roles"},
         * @OA\Response(
         *    response=422,
         *    description="Wrong credentials response",
         *    @OA\JsonContent(
         *       @OA\Property(property="message", type="string", example="Sorry, wrong email address or password. Please try again")
         *        )
         *     )
         * )
    */
    public function delete($id,ValidatorInterface $validator,Helper $helper): Response
    {
        try {
            $em =$this->getDoctrine()->getManager();
            $repository = $this->getDoctrine()->getRepository(Rol::class);
            return $repository->delete($id,$validator,$helper); 
        } catch (Exception $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],500);
        }
    }


    /**
         * @Route("/api/rol/modules", methods={"POST"})
         * @OA\Post(
         * summary="Get Modulo by Rol",
         * description="Get Modulo by Rol",
         * operationId="getmodulobyrol",
         * tags={"Roles"},
         * @OA\RequestBody(
         *    required=true,
         *    description="Data Rol",
         *    @OA\JsonContent(
         *       required={"roles"},
         *       @OA\Property(property="roles", type="array", @OA\Items(type="array",@OA\Items()), example={{"rol":"ROLE_ADMINISTRADOR"},{"rol":"ROLE_ANALISTA"}}),
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
    public function findModuloByRol(Request $request,RolRepository $repository): JsonResponse
    {
        $param = json_decode($request->getContent(),true);

        $data = $repository
        ->findModuloByRol($param);
        return new JsonResponse($data,200);

    }
    
    
    /**
     * Get roleslist rol. 
     * @Route("/api/rol/roleslist", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Returns Rol",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=RolOutPutDto::class))
     *     )
     * )
     * @OA\Tag(name="Roles")
     * @Security(name="Bearer")
     */
    public function findRoleslist(Request $request,RolRepository $repository): JsonResponse
    {
        $param = json_decode($request->getContent(),true);
        $data = $repository
        ->Roleslist();
        if (!$data) {
            return new JsonResponse(['msg'=>'No existen Registros'],200);  
        }   
         return new JsonResponse($data,200);  
    }


}
