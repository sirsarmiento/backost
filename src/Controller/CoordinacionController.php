<?php

namespace App\Controller;

use App\Entity\Coordinacion;
use App\Dto\CoordinacionOutPutDto;
use App\Repository\CoordinacionRepository;
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


class CoordinacionController extends AbstractController
{
     /**
     *  Get list Coordinacion. 
     * @Route("/api/coordinacion/list", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Returns Coordinacion",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=CoordinacionOutPutDto::class))
     *     )
     * )
     * @OA\Tag(name="Coordinacion")
     * @Security(name="Bearer")
     */
    public function findList(Request $request,CoordinacionRepository $repository): JsonResponse
    {
        $data = $repository
        ->findList();
        if (!$data) {
            return new JsonResponse(['msg'=>'No existen Registros'],200);  
        }   
         return new JsonResponse($data,200);  
    }
}
