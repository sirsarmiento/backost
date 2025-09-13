<?php

namespace App\Controller;

use App\Entity\Status;
use App\Dto\StatusOutPutDto;
use App\Form\StatusType;
use App\Dto\DependenciaOutPutDto;
use App\Repository\StatusRepository;
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


class StatusController extends AbstractController
{
     /**
     *  Get list status. 
     * @Route("/api/status/list", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Returns Status",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=StatusOutPutDto::class))
     *     )
     * )
     * @OA\Tag(name="Status")
     * @Security(name="Bearer")
     */
    public function findList(Request $request,StatusRepository $repository): JsonResponse
    {
        $data = $repository
        ->findList();
        if (!$data) {
            return new JsonResponse(['msg'=>'No existen Registros'],200);  
        }   
         return new JsonResponse($data,200);  
    }
 
}
