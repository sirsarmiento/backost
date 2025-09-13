<?php

namespace App\Controller;

use App\Entity\Ciudad;
use App\Form\CiudadType;
use App\Repository\CiudadRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Dto\CiudadOutPutDto;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\Helper;
use Symfony\Component\Validator\Constraints\Json;

class CiudadController extends AbstractController
{
     /**
        * @Route("/api/ciudad/pagined", methods={"POST"})
        * @OA\Post(
         * summary="Ciudad List AllPages",
         * description="Ciudad List All",
         * operationId="Ciudadlistall",
         * tags={"Ciudad"},
         * @OA\RequestBody(
         *    required=true,
         *    description="parametro",
         *    @OA\JsonContent(
         *       required={"page"},
         *       @OA\Property(property="page", type="integer", format="integer", example="1"),
         *       @OA\Property(property="rowByPage", type="integer", format="integer", example="1"),
         *       @OA\Property(property="word", type="integer", format="integer", example="1"),
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
    public function findListAll(Request $request,CiudadRepository $ciudad): JsonResponse
    {
        $param = json_decode($request->getContent(),true);
        $data = $ciudad
        ->findAllPage($param);
        if (!$data) {
            return new JsonResponse(['msg'=>'No existen Registros'],200);  
        }   
         return new JsonResponse($data,200);  
    }

     /**
        * @Route("/api/ciudad/list", methods={"GET"})
        * @OA\Post(
         * summary="Ciudad List",
         * description="Ciudad List",
         * operationId="ciudadlist",
         * tags={"Ciudad"},
         * @OA\RequestBody(
         *    required=true,
         *    description="parametro",
         *    @OA\JsonContent(
         *       required={"page"},
         *       @OA\Property(property="page", type="integer", format="integer", example="1"),
         *       @OA\Property(property="rowByPage", type="integer", format="integer", example="1"),
         *       @OA\Property(property="word", type="integer", format="integer", example="1"),
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
    public function findList(Request $request,CiudadRepository $ciudadRepository): JsonResponse
    {
        $param = json_decode($request->getContent(),true);
        $data = $ciudadRepository
        ->findList();
        if (!$data) {
            return new JsonResponse(['msg'=>'No existen Registros'],200);  
        }   
         return new JsonResponse($data,200);  
    }


         /**
        *  Get Ciudad by Estado Id.
        * @Route("/api/ciudad/estado/{id}", methods={"GET"})
        * @OA\Post(
         * summary="Ciudad List",
         * description="Ciudad List",
         * operationId="Ciudadlist",
         * tags={"Ciudad"},
         * @OA\RequestBody(
         *    required=true,
         *    description="parametro",
         *    @OA\JsonContent(
         *       required={"page"},
         *       @OA\Property(property="page", type="integer", format="integer", example="1"),
         *       @OA\Property(property="rowByPage", type="integer", format="integer", example="1"),
         *       @OA\Property(property="word", type="integer", format="integer", example="1"),
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
         * @OA\Tag(name="Ciudad")
         * @Security(name="Bearer")
    */   
    public function findById($id,Request $request,CiudadRepository $repository): JsonResponse
    {
        $data = $repository
        ->findCiudadByciudad($id);
         return $data;  
    }

    /**
        * @Route("/api/ciudad", methods={"POST"})
        * @OA\Post(
         * summary="Create Ciudad",
         * description="Create Ciudad",
         * operationId="Ciudad",
         * tags={"Ciudad"},
         * @OA\RequestBody(
         *    required=true,
         *    description="parametro",
         *    @OA\JsonContent(
         *       required={"page"},
         *       @OA\Property(property="nombre", type="string", format="string", example="Ciudad Guayana"),
         *       @OA\Property(property="status", type="integer", format="integer", example="1"),
         *       @OA\Property(property="estado", type="integer", format="integer", example="1"),
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

    public function post(Request $request,ValidatorInterface $validator,Helper $helper): Response
    {   
        try {
            $data = json_decode($request->getContent(),true);
            $repository = $this->getDoctrine()->getRepository(Ciudad::class);
            return $repository->post($data,$validator,$helper); 
        } catch (Exception $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],500);
        }
    }


                 /**
        * @Route("/api/ciudad/actualizar/{id}", methods={"PUT"})
        * @OA\Put(
         * summary="Put Ciudad",
         * description="Update Ciudad",
         * operationId="updateCiudad",
         * tags={"Ciudad"},
         * @OA\RequestBody(
         *    required=true,
         *    description="Data Ciudad",
         *    @OA\JsonContent(
         *       required={"nombre","descripcion"},
         *       @OA\Property(property="nombre", type="string", format="string", example="Ciudad Guayana Modificado"),
         *       @OA\Property(property="status", type="integer", format="integer", example="1"),
         *       @OA\Property(property="estado", type="integer", format="integer", example="1"),
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
            $repository = $this->getDoctrine()->getRepository(Ciudad::class);
            return $repository->put($data,$id,$validator,$helper); 
        } catch (Exception $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],500);
        }
    }



}
