<?php

namespace App\Controller;

use App\Dto\TipoCuentaEmailOutPutDto;
use App\Entity\TipoCuenta;
use App\Repository\TipoCuentaEmailRepository;
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



class TipoCuentaEmailController extends AbstractController
{

        /**
        * @Route("/api/tipocuenta/pagined", methods={"POST"})
        * @OA\Post(
         * summary="Tipo Cuenta pagined",
         * description="Tipo Cuenta pagined",
         * operationId="tipocuentaall",
         * tags={"Tipo Cuenta"},
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
    public function findAll(Request $request,TipoCuentaEmailRepository $repository): JsonResponse
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
     *  Get list TipoCuenta. 
     * @Route("/api/encuesta/tipocuenta/list", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Returns Categoria",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=TipoCuentaEmailOutPutDto::class))
     *     )
     * )
     * @OA\Tag(name="Tipo Cuenta")
     * @Security(name="Bearer")
     */
    public function findList(Request $request,TipoCuentaEmailRepository $tipoCuentaEmailRepository): JsonResponse
    {
        $data = $tipoCuentaEmailRepository
        ->findList();
        if (!$data) {
            return new JsonResponse(['msg'=>'No existen Registros'],200);  
        }   
         return new JsonResponse($data,200);  
    }


        /**
        * @Route("/api/tipocuenta", methods={"POST"})
        * @OA\Post(
         * summary="Create Tipo Cuenta Email",
         * description="Create Tipo Cuenta Email",
         * operationId="tipocuentaemail",
         * tags={"Tipo Cuenta"},
         * @OA\RequestBody(
         *    required=true,
         *    description="Data Tipo Cuenta",
         *    @OA\JsonContent(
         *       required={"nombre"},
         *       @OA\Property(property="nombre", type="string", format="string", example="abc.pafar.com.ve:465"),
         *       @OA\Property(property="smtp", type="string", format="string", example="abc.pafar.com.ve:465"),
         *       @OA\Property(property="imap", type="string", format="string", example="abc.pafar.com.ve:993/ssl2"),
         *       @OA\Property(property="pop3", type="string", format="string", example="abc.pafar.com.ve:995"),

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
    public function post(Request $request,TipoCuentaEmailRepository $tipoCuentaEmailRepository,ValidatorInterface $validator,Helper $helper): JsonResponse
    {   
        try {
            $data = json_decode($request->getContent(),true);
            return $tipoCuentaEmailRepository->post($data,$validator,$helper); 
        } catch (Exception $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],500);
        }
    }


    

}
