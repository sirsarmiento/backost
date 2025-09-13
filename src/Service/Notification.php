<?php
namespace App\Service;

/* use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\File; */
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class Notification
{
    private $params;

    public function __construct(ParameterBagInterface $params)

    {
        $this->params = $params;

    }
    public function pushBroadcast($data)
    {
        $param = json_encode($data,true);
        $curl = curl_init();
        curl_setopt_array($curl, [
             CURLOPT_URL => $this->params->get('urlapinotificationpushbroadcast'),
             CURLOPT_RETURNTRANSFER => true,
             CURLOPT_ENCODING => "",
             CURLOPT_MAXREDIRS => 10,
             CURLOPT_TIMEOUT => 30,
             CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
             CURLOPT_CUSTOMREQUEST => "POST",
             CURLOPT_POSTFIELDS => $param,
             CURLOPT_HTTPHEADER => [
               "X-IBM-Client-Id: REPLACE_THIS_KEY",
               "accept: application/json",
               "content-type: application/json; charset=utf-8"
             ],
         ]);
         
     $response = curl_exec($curl);
     $entrgaparam = json_decode($response,true);
     $err = curl_error($curl);

     curl_close($curl);

     if ($err) {
         $data = array("result"=>false, "response"=>"Error en la llamada notification/push/broadcast ".$err);
         return new JsonResponse(['msg'=>"Error ".$err],500);
     } else {
        //return new JsonResponse($entrgaparam);
        return true;
     } 


    }


}