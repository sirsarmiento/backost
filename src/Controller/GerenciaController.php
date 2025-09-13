<?php

namespace App\Controller;

use App\Entity\Gerencia;
use App\Dto\GerenciaOutPutDto;
use App\Repository\GerenciaRepository;
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


class GerenciaController extends AbstractController
{
     /**
     *  Get list Gerencia. 
     * @Route("/api/gerencia/list", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Returns Gerencia",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=GerenciaOutPutDto::class))
     *     )
     * )
     * @OA\Tag(name="Gerencia")
     * @Security(name="Bearer")
     */
    public function findList(Request $request,GerenciaRepository $repository): JsonResponse
    {
        $data = $repository
        ->findList();
        if (!$data) {
            return new JsonResponse(['msg'=>'No existen Registros'],200);  
        }   
         return new JsonResponse($data,200);  
    }
}
