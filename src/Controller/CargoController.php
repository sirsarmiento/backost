<?php

namespace App\Controller;

use App\Entity\Cargo;
use App\Dto\CargoOutPutDto;
use App\Repository\CargoRepository;
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




class CargoController extends AbstractController
{


       /**
        * @Route("/api/cargo/pagined", methods={"POST"})
        * @OA\Post(
         * summary="Cargo pagined",
         * description="Cargo pagined",
         * operationId="cargopagined",
         * tags={"Cargos"},
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
    */    public function pagined(Request $request,CargoRepository $repository): JsonResponse
    {
        $param = json_decode($request->getContent(),true);

        $data = $repository
        ->findPagined($param);
        if (!$data) {
            return new JsonResponse(['msg'=>'No existen Registros'],200);  
        }   
         return new JsonResponse($data,200);  
    }


       /**
        * @Route("/api/cargo/all", methods={"GET"})
        * @OA\Get(
         * summary="Cargo All",
         * description="Cargo All",
         * operationId="cargoall",
         * tags={"Cargos"},
         * @OA\Response(
         *    response=422,
         *    description="Wrong credentials response",
         *    @OA\JsonContent(
         *       @OA\Property(property="message", type="string", example="Sorry, wrong email address or password. Please try again")
         *        )
         *     )
         * )
    */    public function findAll(Request $request,CargoRepository $repository): JsonResponse
    {
        $param = json_decode($request->getContent(),true);

        $data = $repository
        ->findAllPage($param);
        if (!$data) {
            return new JsonResponse(['msg'=>'No existen Registros'],200);  
        }   
         return new JsonResponse($data,200);  
    }


     /**
     *  Get list cargos. 
     * @Route("/api/cargo/list", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Returns Cargos",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=CargoOutPutDto::class))
     *     )
     * )
     * @OA\Tag(name="Cargos")
     * @Security(name="Bearer")
     */
    public function findList(Request $request,CargoRepository $repository): JsonResponse
    {
        $data = $repository
        ->findList();
        if (!$data) {
            return new JsonResponse(['msg'=>'No existen Registros'],200);  
        }   
         return new JsonResponse($data,200);  
    }
    
        /**
        * @Route("/api/cargo", methods={"POST"})
        * @OA\Post(
         * summary="Create Cargo",
         * description="Create Cargo",
         * operationId="createcargo",
         * tags={"Cargos"},
         * @OA\RequestBody(
         *    required=true,
         *    description="Data Cargo",
         *    @OA\JsonContent(
         *       required={"descripcion"},
         *       @OA\Property(property="descripcion", type="string", example="Analista"),
         *       @OA\Property(property="nivel", type="integer", example="1")
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
    public function post(Request $request,ValidatorInterface $validator,Helper $helper,CargoRepository $repository): JsonResponse
    {   
        try {
            $data = json_decode($request->getContent(),true);
            return $repository->post($data,$validator,$helper); 
        } catch (Exception $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],500);
        }
    }


        
        /**
        * @Route("/api/cargo/{id}", methods={"PUT"})
        * @OA\Put(
         * summary="Actualiza Cargo",
         * description="ActualizaCargo",
         * operationId="actualiza",
         * tags={"Cargos"},
         * @OA\RequestBody(
         *    required=true,
         *    description="Actualiza Cargo",
         *    @OA\JsonContent(
         *       required={"descripcion"},
         *       @OA\Property(property="descripcion", type="string", example="Analista"),
         *       @OA\Property(property="nivel", type="integer", example="1")
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
    public function put($id,Request $request,ValidatorInterface $validator,Helper $helper,CargoRepository $repository): JsonResponse
    {   
        try {
            $data = json_decode($request->getContent(),true);
            return $repository->put($data,$id,$validator,$helper);
        } catch (Exception $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],500);
        }
    }


        /**
        * @Route("/api/cargo/{id}", methods={"DELETE"})
        * @OA\Delete(
         * summary="Delete Cargo",
         * description="Delete Cargo",
         * operationId="deletecargo",
         * tags={"Cargos"},
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
            $repository = $this->getDoctrine()->getRepository(Cargo::class);
            return $repository->delete($id,$validator,$helper); 
        } catch (Exception $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],500);
        }
    }

}
