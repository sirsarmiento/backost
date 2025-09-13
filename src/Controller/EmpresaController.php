<?php

namespace App\Controller;

use App\Entity\Empresa;
use App\Repository\EmpresaRepository;
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

class EmpresaController extends AbstractController
{
    /**
        *  Get Pais by Pais Id.
        * @Route("/api/empresa/List", methods={"GET"})
        * @OA\Post(
         * summary="Empresa List",
         * description="Empresa List",
         * operationId="Empresalist",
         * tags={"Empresa"},
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
         * @OA\Tag(name="Empresa")
         * @Security(name="Bearer")
    */  

    public function findList(Request $request,EmpresaRepository $empresaRepository): JsonResponse
    {
        $param = json_decode($request->getContent(),true);
        $data = $empresaRepository
        ->findList();
        if (!$data) {
            return new JsonResponse(['msg'=>'No existen Registros'],200);  
        }   
         return new JsonResponse($data,200);  
    }


    /**
        * @Route("/api/empresa", methods={"POST"})
        * @OA\Post(
         * summary="Create Empresa",
         * description="Create Empresa",
         * operationId="Empresa",
         * tags={"Empresa"},
         * @OA\RequestBody(
         *    required=true,
         *    description="parametro",
         *    @OA\JsonContent(
         *       required={"page"},
         *       @OA\Property(property="nombre", type="string", format="string", example="Polar C.A."),
         *       @OA\Property(property="status", type="integer", format="integer", example="1"),
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
            $repository = $this->getDoctrine()->getRepository(Empresa::class);
            return $repository->post($data,$validator,$helper); 
        } catch (Exception $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],500);
        }
    }

    /**
        * @Route("/api/empresa/actualizar/{id}", methods={"PUT"})
        * @OA\Put(
         * summary="Put Empresa",
         * description="Update Empresa",
         * operationId="updateEmpresa",
         * tags={"Empresa"},
         * @OA\RequestBody(
         *    required=true,
         *    description="Data Empresa",
         *    @OA\JsonContent(
         *       required={"nombre","descripcion"},
         *       @OA\Property(property="nombre", type="string", format="string", example="Polar C.A Modificado"),
         *       @OA\Property(property="status", type="integer", format="integer", example="1"),
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
            $repository = $this->getDoctrine()->getRepository(Empresa::class);
            return $repository->put($data,$id,$validator,$helper); 
        } catch (Exception $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],500);
        }
    }

    /**
        * Get Resumen By Empresa.
        * @Route("/api/empresa/resumen", methods={"GET"})
        * @OA\Post(
         * summary="Resumen List",
         * description="Resumen List",
         * operationId="ResumenList",
         * tags={"Empresa"},
         * @OA\RequestBody(
         *    @OA\JsonContent(
         *       required={"page"},
         *       @OA\Property(property="totalProcesses", type="integer", format="integer", example="1"),
         *       @OA\Property(property="totalRisks", type="integer", format="integer", example="1"),
         *       @OA\Property(property="totalControls", type="integer", format="integer", example="1"),
         *       @OA\Property(property="totalEvents", type="integer", format="integer", example="1"),
         *       @OA\Property(property="totalPlans", type="integer", format="integer", example="1"),
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
         * @OA\Tag(name="Empresa")
         * @Security(name="Bearer")
    */  

    public function resumen(Request $request,EmpresaRepository $empresaRepository): JsonResponse
    {
        $param = json_decode($request->getContent(),true);
        $data = $empresaRepository
        ->resumen();
        if (!$data) {
            return new JsonResponse(['msg'=>'No existen Registros'],200);  
        }   
         return new JsonResponse($data,200);  
    }


}
