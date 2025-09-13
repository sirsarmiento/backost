<?php

namespace App\Controller;

use App\Entity\ModuloRol;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Dto\ModuloRolOutPutDto;

use App\Repository\ModuloRolRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\Helper;
use Symfony\Component\Validator\Constraints\Json;



class ModuloRolController extends AbstractController
{
        /**
        * @Route("/api/modulo/rol/pagined", methods={"POST"})
        * @OA\Post(
         * summary="Modulo Rol pagined",
         * description="Modulo Rol pagined",
         * operationId="ModuloRolall",
         * tags={"ModulosRol"},
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
    public function findAllModuloRol(Request $request,ModuloRolRepository $repository): JsonResponse
    {
        $param = json_decode($request->getContent(),true);

        $data = $repository
        ->findAllModuloRol($param);
        if (!$data) {
            return new JsonResponse(['msg'=>'No existen Registros'],404);  
        }   
         return new JsonResponse($data,200);  
    }



    
    /**
     *  Get an Modulo Rol. 
     * @Route("/api/modulo/rol/{id}", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Returns module",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=ModuloRolOutPutDto::class))
     *     )
     * )
     * @OA\Tag(name="ModulosRol")
     * @Security(name="Bearer")
     */
    public function findById($id, ModuloRolRepository $repository): JsonResponse
    {
        $data = $repository
        ->findById($id);
        if (!$data) {
            return new JsonResponse(['msg'=>'No existen Registros'],200);  
        }   
         return new JsonResponse($data,200);  
    }


        /**
        * @Route("/api/modulo/rol", methods={"POST"})
        * @OA\Post(
         * summary="Create Modulo Rol Autorizacion",
         * description="Create Modulo Rol Autorizaciono",
         * operationId="CreateModuloRolAutorizacion",
         * tags={"ModulosRol"},
         * @OA\RequestBody(
         *    required=true,
         *    description="Data Modulo Rol Autorizaciones",
         *    @OA\JsonContent(
         *       required={"rol"},
         *       @OA\Property(property="rol", type="integer", example="1"),
         *       @OA\Property(property="autorizaciones", type="array", @OA\Items(type="array",@OA\Items()), example={{"permiso":"Incluir"},{"permiso":"Actualizar"}}),
         *       @OA\Property(property="modulo", type="integer", example="1"),
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
    public function post(Request $request,ValidatorInterface $validator,Helper $helper,ModuloRolRepository $repository): JsonResponse
    {   
        try {
            $data = json_decode($request->getContent(),true);
            return $repository->post($data,$validator,$helper); 
        } catch (Exception $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],500);
        }
    }
    

       /**
        * @Route("/api/modulo/rol/{id}", methods={"PUT"})
        * @OA\Put(
         * summary="Put Modulo Rol",
         * description="Update Modulo Rol",
         * operationId="updateModuloRol",
         * tags={"ModulosRol"},
         * @OA\RequestBody(
         *    required=true,
         *    description="Data Modulo Rol",
         *    @OA\JsonContent(
         *       required={"rol","modulo"},
         *       @OA\Property(property="rol", type="integer", example="1"),
         *       @OA\Property(property="autorizaciones", type="array", @OA\Items(type="array",@OA\Items()), example={{"permiso":"Incluir"},{"permiso":"Actualizar"}}),
         *       @OA\Property(property="modulo", type="integer", example="1"),
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
    public function put($id,Request $request,ValidatorInterface $validator,Helper $helper,ModuloRolRepository $repository): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(),true);
            $em =$this->getDoctrine()->getManager();
            return $repository->put($data,$id,$validator,$helper); 
        } catch (Exception $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],409);
        }
    }


        /**
        * @Route("/api/modulo/rol/{id}", methods={"DELETE"})
        * @OA\Delete(
         * summary="Delete Modulo Rol",
         * description="Delete Modulo Rol",
         * operationId="deletemoduloRol",
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
    public function delete($id,ValidatorInterface $validator,Helper $helper,ModuloRolRepository $repository): JsonResponse
    {
        try {
            $em =$this->getDoctrine()->getManager();
            return $repository->delete($id,$validator); 
        } catch (Exception $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],500);
        }
    }
}
