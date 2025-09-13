<?php

namespace App\Controller;

use App\Entity\Dependencia;
use App\Form\DependenciaType;
use App\Dto\DependenciaOutPutDto;
use App\Repository\DependenciaRepository;
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


class DependenciaController extends AbstractController
{
     /**
     *  Get list Dependencia. 
     * @Route("/api/dependencia/list", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Returns Dependencia",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=DependenciaOutPutDto::class))
     *     )
     * )
     * @OA\Tag(name="Dependencia")
     * @Security(name="Bearer")
     */
    public function findList(Request $request,DependenciaRepository $repository): JsonResponse
    {
        $data = $repository
        ->findList();
        if (!$data) {
            return new JsonResponse(['msg'=>'No existen Registros'],200);  
        }   
         return new JsonResponse($data,200);  
    }


    


    /**
        * @Route("/api/dependencia", methods={"POST"})
        * @OA\Post(
         * summary="Create Dependencia",
         * description="Create Dependencia",
         * operationId="dependencia",
         * tags={"Dependencia"},
         * @OA\RequestBody(
         *    required=true,
         *    description="Data Dependencia",
         *    @OA\JsonContent(
         *       required={"username","password"},
         *       @OA\Property(property="username", type="string", format="string", example="Maria Reyes"),
         *       @OA\Property(property="idStatus", type="integer", example=1),
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
            $repository = $this->getDoctrine()->getRepository(Dependencia::class);
            return $repository->post($data,$validator,$helper); 
        } catch (Exception $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],500);
        }
    }

}
