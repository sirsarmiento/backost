<?php

namespace App\Controller;

use App\Entity\Nivel;
use App\Dto\NivelOutPutDto;
use App\Repository\NivelRepository;
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




class NivelController extends AbstractController
{


       /**
        * @Route("/api/nivel/pagined", methods={"POST"})
        * @OA\Post(
         * summary="Nivel All",
         * description="Nivel All",
         * operationId="nivelall",
         * tags={"Nivel"},
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
    */    public function findAll(Request $request,NivelRepository $repository): JsonResponse
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
     *  Get list nivel. 
     * @Route("/api/nivel/list", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Returns Nivel",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=NivelOutPutDto::class))
     *     )
     * )
     * @OA\Tag(name="Nivel")
     * @Security(name="Bearer")
     */
    public function findList(Request $request,NivelRepository $repository): JsonResponse
    {
        $data = $repository
        ->findList();
        if (!$data) {
            return new JsonResponse(['msg'=>'No existen Registros'],200);  
        }   
         return new JsonResponse($data,200);  
    }
    
        /**
        * @Route("/api/nivel", methods={"POST"})
        * @OA\Post(
         * summary="Create Nivel",
         * description="Create Nivel",
         * operationId="createnivel",
         * tags={"Nivel"},
         * @OA\RequestBody(
         *    required=true,
         *    description="Data Nivel",
         *    @OA\JsonContent(
         *       required={"nombre"},
         *       @OA\Property(property="Nombre", type="string", example="Estrategico"),
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
    public function post(Request $request,ValidatorInterface $validator,Helper $helper,NivelRepository $repository): JsonResponse
    {   
        try {
            $data = json_decode($request->getContent(),true);
            return $repository->post($data,$validator,$helper); 
        } catch (Exception $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],500);
        }
    }


        
        /**
        * @Route("/api/nivel/{id}", methods={"PUT"})
        * @OA\Put(
         * summary="Actualiza Nivel",
         * description="ActualizaNivel",
         * operationId="actualizanivel",
         * tags={"Nivel"},
         * @OA\RequestBody(
         *    required=true,
         *    description="Actualiza Nivel",
         *    @OA\JsonContent(
         *       required={"Nombre"},
         *       @OA\Property(property="Nombre", type="string", example="Analista"),
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
    public function put($id,Request $request,ValidatorInterface $validator,Helper $helper,NivelRepository $repository): JsonResponse
    {   
        try {
            $data = json_decode($request->getContent(),true);
            return $repository->put($data,$id,$validator,$helper);
        } catch (Exception $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],500);
        }
    }


        /**
        * @Route("/api/nivel/{id}", methods={"DELETE"})
        * @OA\Delete(
         * summary="Delete",
         * description="Delete",
         * operationId="deletenivel",
         * tags={"Nivel"},
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
            $repository = $this->getDoctrine()->getRepository(Nivel::class);
            return $repository->delete($id,$validator,$helper); 
        } catch (Exception $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],500);
        }
    }

}
