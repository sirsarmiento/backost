<?php
namespace App\Controller\Notificacion;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

//use App\Repository\Notificacion\NotificacionRepository;

use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\Helper;
use Symfony\Component\Validator\Constraints\Json;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;
use Symfony\Component\Mime\MimeTypes;

class NotificacionController extends AbstractController
{

    private $params;

    public function __construct(ParameterBagInterface $params)

    {
        $this->params = $params;

    }


        /**
        * @Route("/api/notification/all/pagined", methods={"POST"})
        * @OA\Post(
         * summary="Notificacion Pagined",
         * description="Notificacion Pagined",
         * operationId="NotificacionAll",
         * tags={"Notificacion"},
         * @OA\RequestBody(
         *    required=true,
         *    description="parametro",
         *    @OA\JsonContent(
         *       required={"page"},
         *       @OA\Property(property="page", type="integer", format="integer", example=1),
         *       @OA\Property(property="rowByPage", type="integer", format="integer", example=10),
         *       @OA\Property(property="word", type="integer", format="integer", example="null"),
         *       @OA\Property(property="email", type="string", format="integer", example="sirjcbg1@hotmail.com"),
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
    public function findAll(Request $request): JsonResponse
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $param = json_decode($request->getContent(),true);
        $rowPage = $param['page'];
        $rowByPage = $param['rowByPage'];
        $rowWord = $param['word'];
        $rowEmail = $param['email'];
        $curl = curl_init();
        curl_setopt_array($curl, [
             CURLOPT_URL => $this->params->get('urlapinotification'),
             CURLOPT_RETURNTRANSFER => true,
             CURLOPT_ENCODING => "",
             CURLOPT_MAXREDIRS => 10,
             CURLOPT_TIMEOUT => 30,
             CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
             CURLOPT_CUSTOMREQUEST => "POST",
             CURLOPT_POSTFIELDS => "{\"page\":\"$rowPage\",\"rowByPage\":\"$rowByPage\",\"word\":\"$rowWord\",\"email\":\"$rowEmail\"}",
             CURLOPT_HTTPHEADER => [
               "X-IBM-Client-Id: REPLACE_THIS_KEY",
               "accept: application/json",
               "content-type: application/json"
             ],
         ]);
         
     $response = curl_exec($curl);
     $entrgaparam = json_decode($response,true);
     $err = curl_error($curl);

     curl_close($curl);

     if ($err) {
         $data = array("result"=>false, "response"=>"Error en la llamada notification/all/pagined ".$err);
         return new JsonResponse(['msg'=>"Error ".$err],500);
     } else {
        return new JsonResponse($entrgaparam);
     }

    }


    /**
        * @Route("/api/notification/all/pagined_withoutreading", methods={"POST"})
        * @OA\Post(
         * summary="Notificación Pagined Sin Leer",
         * description="Notificación Pagined Sin Leer",
         * operationId="NotificacionAllWithoutReading",
         * tags={"Notificacion"},
         * @OA\RequestBody(
         *    required=true,
         *    description="parametro",
         *    @OA\JsonContent(
         *       required={"page"},
         *       @OA\Property(property="page", type="integer", format="integer", example=1),
         *       @OA\Property(property="rowByPage", type="integer", format="integer", example=10),
         *       @OA\Property(property="word", type="integer", format="integer", example="null"),
         *       @OA\Property(property="email", type="string", format="integer", example="sirjcbg1@hotmail.com"),
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
    public function findAllWithoutReading(Request $request): JsonResponse
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $param = json_decode($request->getContent(),true);
        $rowPage = $param['page'];
        $rowByPage = $param['rowByPage'];
        $rowWord = $param['word'];
        $rowEmail = $param['email'];
        $curl = curl_init();
        curl_setopt_array($curl, [
             CURLOPT_URL => $this->params->get('urlapipaginedWithoutReading'),
             CURLOPT_RETURNTRANSFER => true,
             CURLOPT_ENCODING => "",
             CURLOPT_MAXREDIRS => 10,
             CURLOPT_TIMEOUT => 30,
             CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
             CURLOPT_CUSTOMREQUEST => "POST",
             CURLOPT_POSTFIELDS => "{\"page\":\"$rowPage\",\"rowByPage\":\"$rowByPage\",\"word\":\"$rowWord\",\"email\":\"$rowEmail\"}",
             CURLOPT_HTTPHEADER => [
               "X-IBM-Client-Id: REPLACE_THIS_KEY",
               "accept: application/json",
               "content-type: application/json"
             ],
         ]);
         
     $response = curl_exec($curl);
     $entrgaparam = json_decode($response,true);
     $err = curl_error($curl);

     curl_close($curl);

     if ($err) {
         $data = array("result"=>false, "response"=>"Error en la llamada notification/all/pagined ".$err);
         return new JsonResponse(['msg'=>"Error ".$err],500);
     } else {
        return new JsonResponse($entrgaparam);
     }

    }


     /**
        * @Route("/api/notification/deleteNotificacion/{id}", methods={"DELETE"})
        * @OA\Delete(
         * summary="Delete Notification",
         * description="Delete Notification",
         * operationId="deletenotification",
         * tags={"Notificacion"},
         * @OA\Response(
         *    response=422,
         *    description="Wrong credentials response",
         *    @OA\JsonContent(
         *       @OA\Property(property="message", type="string", example="Sorry, wrong email address or password. Please try again")
         *        )
         *     )
         * )
    */
    public function deleteNotificacion($id,ValidatorInterface $validator,Helper $helper): Response
    {
        try {
            $rowId = $id;
            $curl = curl_init();
            curl_setopt_array($curl, [
                 CURLOPT_URL => $this->params->get('urlapideletenotification'),
                 CURLOPT_RETURNTRANSFER => true,
                 CURLOPT_ENCODING => "",
                 CURLOPT_MAXREDIRS => 10,
                 CURLOPT_TIMEOUT => 30,
                 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                 CURLOPT_CUSTOMREQUEST => "DELETE",
                 CURLOPT_POSTFIELDS => "{\"idnotification\":\"$rowId\"}",
                 CURLOPT_HTTPHEADER => [
                   "X-IBM-Client-Id: REPLACE_THIS_KEY",
                   "accept: application/json",
                   "content-type: application/json"
                 ],
             ]);
             
         $response = curl_exec($curl);
         $entrgaparam = json_decode($response,true);
         $err = curl_error($curl);
    
         curl_close($curl);
    
         if ($err) {
             $data = array("result"=>false, "response"=>"Error en la llamada notification/all/pagined ".$err);
             return new JsonResponse(['msg'=>"Error ".$err],500);
         } else {
            return new JsonResponse($entrgaparam);
         } 

        } catch (HttpException $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],500);
        }
    }


    /**
        * @Route("/api/notification/deleteAll/{email}", methods={"DELETE"})
        * @OA\Delete(
         * summary="Delete All Notification",
         * description="Delete All Notification",
         * operationId="deleteallnotification",
         * tags={"Notificacion"},
         * @OA\Response(
         *    response=422,
         *    description="Wrong credentials response",
         *    @OA\JsonContent(
         *       @OA\Property(property="message", type="string", example="Sorry, wrong email address or password. Please try again")
         *        )
         *     )
         * )
    */
    public function deleteall($email,ValidatorInterface $validator,Helper $helper): Response
    {
        try {
            $rowemail = $email;
            $curl = curl_init();
            curl_setopt_array($curl, [
                 CURLOPT_URL => $this->params->get('urlapideleteallnotification'),
                 CURLOPT_RETURNTRANSFER => true,
                 CURLOPT_ENCODING => "",
                 CURLOPT_MAXREDIRS => 10,
                 CURLOPT_TIMEOUT => 30,
                 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                 CURLOPT_CUSTOMREQUEST => "DELETE",
                 CURLOPT_POSTFIELDS => "{\"destinatary\":\"$rowemail\"}",
                 CURLOPT_HTTPHEADER => [
                   "X-IBM-Client-Id: REPLACE_THIS_KEY",
                   "accept: application/json",
                   "content-type: application/json"
                 ],
             ]);
             
         $response = curl_exec($curl);
         $entrgaparam = json_decode($response,true);
         $err = curl_error($curl);
    
         curl_close($curl);
    
         if ($err) {
             $data = array("result"=>false, "response"=>"Error en la llamada notification/all/pagined ".$err);
             return new JsonResponse(['msg'=>"Error ".$err],500);
         } else {
            return new JsonResponse($entrgaparam);
         } 

        } catch (HttpException $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],500);
        }
    }




}