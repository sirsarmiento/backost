<?php

namespace App\Controller;

use App\Entity\CuentaEmail;
use App\Repository\CuentaEmailRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Dto\CuentaEmailOutPutDto;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\Helper;
use Symfony\Component\Validator\Constraints\Json;


use App\Repository\TipoCuentaEmailRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;



class CuentaEmailController extends AbstractController
{
            /**
        * @Route("/api/cuenta/pagined", methods={"POST"})
        * @OA\Post(
         * summary="Cuenta pagined",
         * description="Cuenta pagined",
         * operationId="cuentaall",
         * tags={"Cuenta Email"},
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
    public function findAll(Request $request,CuentaEmailRepository $repository): JsonResponse
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
        * @Route("/api/cuenta", methods={"POST"})
        * @OA\Post(
         * summary="Create Tipo Cuenta Email",
         * description="Create Tipo Cuenta Email",
         * operationId="cuentaemail",
         * tags={"Cuenta Email"},
         * @OA\RequestBody(
         *    required=true,
         *    description="Data Tipo Cuenta",
         *    @OA\JsonContent(
         *       required={"nombre"},
         *       @OA\Property(property="nombre", type="string", format="string", example="mi cuenta"),
         *       @OA\Property(property="email", type="string", format="string", example="sir2333@gmail.com"),
         *       @OA\Property(property="password", type="string", format="string", example="a3k3h3h33sss"),
         *       @OA\Property(property="status", type="integer", format="integer", example="1"),
         *       @OA\Property(property="tipoCuenta", type="integer", format="integer", example="1"),
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
    public function post(Request $request,CuentaEmailRepository $tipoCuentaEmailRepository,ValidatorInterface $validator,Helper $helper): JsonResponse
    {   
        try {
            $data = json_decode($request->getContent(),true);
            return $tipoCuentaEmailRepository->post($data,$validator,$helper); 
        } catch (Exception $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],500);
        }
    }


            /**
        * @Route("/api/cuenta/{id}", methods={"PUT"})
        * @OA\Put(
         * summary="Edit Tipo Cuenta Email",
         * description="Edit Tipo Cuenta Email",
         * operationId="editcuentaemail",
         * tags={"Cuenta Email"},
         * @OA\RequestBody(
         *    required=true,
         *    description="Data Tipo Cuenta",
         *    @OA\JsonContent(
         *       required={"nombre"},
         *       @OA\Property(property="nombre", type="string", format="string", example="mi cuenta"),
         *       @OA\Property(property="email", type="string", format="string", example="sir2333@gmail.com"),
         *       @OA\Property(property="password", type="string", format="string", example="a3k3h3h33sss"),
         *       @OA\Property(property="status", type="integer", format="integer", example="1"),
         *       @OA\Property(property="tipoCuenta", type="integer", format="integer", example="1"),
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
    public function put($id,Request $request,CuentaEmailRepository $tipoCuentaEmailRepository,ValidatorInterface $validator,Helper $helper): JsonResponse
    {   
        try {
            $data = json_decode($request->getContent(),true);
            return $tipoCuentaEmailRepository->put($data,$id,$validator,$helper); 
        } catch (Exception $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],500);
        }
    }


    /**
     *  Get an user. 
     * @Route("/api/cuenta/{id}", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Returns module",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=CuentaEmailOutPutDto::class))
     *     )
     * )
     * @OA\Tag(name="Cuenta Email")
     * @Security(name="Bearer")
     */
    public function findById($id,CuentaEmailRepository $repository): JsonResponse
    {
        $data = $repository
        ->findById($id);
        if (!$data) {
            return new JsonResponse(['msg'=>'No existen Registros'],200);  
        }   
         return new JsonResponse($data,200);  
    }


    /**
        * @Route("/api/cuenta/{id}", methods={"DELETE"})
        * @OA\Delete(
         * summary="Cuenta Email",
         * description="Cuenta Email",
         * operationId="deletecuenta",
         * tags={"Cuenta Email"},
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
            $repository = $this->getDoctrine()->getRepository(CuentaEmail::class);
            return $repository->delete($id,$validator,$helper); 
        } catch (Exception $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],500);
        }
    }



}
