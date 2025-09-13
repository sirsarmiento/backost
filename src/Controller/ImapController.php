<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CuentaEmailRepository;

use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\Security\Core\Security;
use OpenApi\Annotations as OA;
use PhpParser\Node\Stmt\TryCatch;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints\Json;
use UnexpectedValueException;

class ImapController extends AbstractController
{

    private $charset,$htmlmsg,$plainmsg,$attachments;
    private $hasAttachmend;
    private $security;
    public function __construct(Security $security,CuentaEmailRepository $repository)
    {
        $this->security = $security;
    }



        /**
        * @Route("/api/imap/headers/{buzonId}", methods={"GET"})
        * @OA\Get(
         * summary="Get headers emails",
         * description="Get headers emails",
         * operationId="Getheadersemails",
         * tags={"Imap"},
         * @OA\Response(
         *    response=422,
         *    description="Wrong credentials response",
         *    @OA\JsonContent(
         *       @OA\Property(property="message", type="string", example="Sorry, wrong email address or password. Please try again")
         *        )
         *     )
         * )
    */
    public function getHeaders($buzonId,CuentaEmailRepository $repository): JsonResponse
    {
        $buzon = $repository->findOneBuzonByIdAndIdUser($buzonId,$this->security->getUser()->getId())[0];

        if(is_null($buzon->getTipoCuenta()["imap"]))
        return [];
            
        $imapServer= "{". $buzon->getTipoCuenta()["imap"]."}";
        $password=$buzon->getPassword();
        $email=$buzon->getEmail();

        $mailbox = new \PhpImap\Mailbox(
            "$imapServer", // IMAP server and mailbox folder
            "$email", // Username for the before configured mailbox
            "$password", // Password for the before configured username
            __DIR__, // Directory, where attachments will be saved (optional)
            'UTF-8', // Server encoding (optional)
            true, // Trim leading/ending whitespaces of IMAP path (optional)
            false // Attachment filename mode (optional; false = random filename; true = original filename)
        );        
        try {
            $mailsIds = $mailbox->searchMailbox('ALL');
            $dataEmail=array();
            $dataHeaders=array();
            foreach($mailsIds as $num) {                
                    $head = $mailbox->getMailHeader($num);
                    $dataEmail["from"]=isset($head->fromName)?$head->fromName:'';
                    $dataEmail["Address"]=isset($head->fromAddress)?$head->fromAddress:'';
                    $dataEmail["num"]=$num;
                    $dataEmail["date"]=$head->date;
                    $dataEmail["subject"] =$head->subject;
                    $markAsSeen = false;
                    $mail = $mailbox->getMail($num, $markAsSeen);
                    if(count($mail->getAttachments())>0){
                        $dataEmail["attachment"]=1;
                    }else{
                        $dataEmail["attachment"]=0;
                    }
                    $dataHeaders[]=$dataEmail;
            }
            $mailbox->disconnect();
            return new JsonResponse($dataHeaders,200);  
        } catch(\PhpImap\Exceptions\ConnectionException $ex) {
            $error= array("error"=>"IMAP connection failed: " . implode(",", $ex->getErrors('all')));
            return new JsonResponse($error,500);  

        }
    }


       /**
        * @Route("/api/imap/mail/{buzonId}/{num}", methods={"GET"})
        * @OA\Get(
         * summary="Get Mail",
         * description="GettailMail",
         * operationId="GetdetailMail",
         * tags={"Imap"},
         * @OA\Response(
         *    response=422,
         *    description="Wrong credentials response",
         *    @OA\JsonContent(
         *       @OA\Property(property="message", type="string", example="Sorry, wrong email address or password. Please try again")
         *        )
         *     )
         * )
    */
    public function getMail($buzonId,$num,CuentaEmailRepository $repository): JsonResponse
    {

        $mailBox=$this->getEmailConnectLibrary($buzonId,$repository);
        if(!$mailBox){

            return new JsonResponse(array("error"=>"conexion no establecida,credenciales invalidas"),409);            
        }
            

//        try {
            $mailsIds = $mailBox->searchMailbox('ALL');
            $mail=$mailBox->getMail($num);
            // $body=$this->getmsg($mailBox,36);
            // $s = imap_fetchstructure($mailBox,36);
            // $this->getAttachmend($s,$mailBox,36);
            // $dataEmail=array();
           
            // var_dump($this->htmlmsg,$this->plainmsg,$this->charset);
            // imap_close($mailBox);
                $dataEmail=[];
                return new JsonResponse($dataEmail,200);  

        // } catch(\ErrorException $ex) {
        //         $error = array("error"=>$ex->getMessage());
        //         return new JsonResponse($error,500);  
     
        // } catch(\PhpImap\Exceptions\ConnectionException $ex) {
        //     $error= array("error"=>"IMAP connection failed: " . implode(",", $ex->getErrors('all')));
        //     return new JsonResponse($error,500);  
        // } 
    }



    

function getmsg($mbox,$mid) {

   
    // BODY
    $s = imap_fetchstructure($mbox,$mid);
    if (!$s->parts)  // simple
        $this->getpart($mbox,$mid,$s,0);  // pass 0 as part-number
    else {  // multipart: cycle through each part
        foreach ($s->parts as $partno0=>$p)
            $this->getpart($mbox,$mid,$p,$partno0+1);
    }
}

    function getpart($mbox,$mid,$p,$partno) {
        // $partno = '1', '2', '2.1', '2.1.3', etc for multipart, 0 if simple
    
        // DECODE DATA
        $data = ($partno)?
            imap_fetchbody($mbox,$mid,$partno):  // multipart
            imap_body($mbox,$mid);  // simple
        // Any part may be encoded, even plain text messages, so check everything.
        if ($p->encoding==4)
            $data = quoted_printable_decode($data);
        elseif ($p->encoding==3)
            $data = base64_decode($data);
    
        // PARAMETERS
        // get all parameters, like charset, filenames of attachments, etc.
        $params = array();
        
        if ($p->ifparameters)
            foreach ($p->parameters as $x)
                $params[strtolower($x->attribute)] = $x->value;
        if ($p->ifdparameters)
            foreach ($p->parameters as $x)
                $params[strtolower($x->attribute)] = $x->value;
    

        // TEXT
        if ($p->type==0 && $data) {
            // Messages may be split in different parts because of inline attachments,
            // so append parts together with blank row.
            if (strtolower($p->subtype)=='plain')
                $this->plainmsg.= trim($data) ."\n\n";
            else
                $this->htmlmsg.= $data ."<br><br>";
                $this->charset = $params['charset'];  // assume all parts are same charset
        }
    
        // EMBEDDED MESSAGE
        // Many bounce notifications embed the original message as type 2,
        // but AOL uses type 1 (multipart), which is not handled here.
        // There are no PHP functions to parse embedded messages,
        // so this just appends the raw source to the main message.
        elseif ($p->type==2 && $data) {
            $this->plainmsg.= $data."\n\n";
        }
        // SUBPART RECURSION
        if(isset($p->parts))
        if ($p->parts) {
            foreach ($p->parts as $partno0=>$p2)
                $this->getpart($mbox,$mid,$p2,$partno.'.'.($partno0+1));  // 1.2, 1.2.1, etc.
        }
    }
    
   
    
       /**
        * @Route("/api/imap/check/mailboxe", methods={"GET"})
        * @OA\Get(
         * summary="Get Mail",
         * description="Get Mail",
         * operationId="getmailboxes",
         * tags={"Imap"},
         * @OA\Response(
         *    response=422,
         *    description="Wrong credentials response",
         *    @OA\JsonContent(
         *       @OA\Property(property="message", type="string", example="Sorry, wrong email address or password. Please try again")
         *        )
         *     )
         * )
    */
    public function getMailBoxes(CuentaEmailRepository $repository): JsonResponse
    {
        $buzones= $repository->findByUser($this->security->getUser()->getId());
        $dataBuzones=array();  
        foreach($buzones as $buzon){
            $dataBuzones[]=array("id"=>$buzon->getId(),
            "nombre"=>$buzon->getTipoCuenta()["nombre"],
             "infoBuzon"=>$this->getInfoMailBoxes($buzon->getId(),$repository)
            );    
        }
        return new JsonResponse($dataBuzones,200);  
    }



    




    private function getInfoMailBoxes($buzonId,$repository){
        $mailBox=$this->getEmailConnect($buzonId,$repository);
        if(!$mailBox){
            return array("error"=>"conexion no establecida,credenciales invalidas");            
        }
        $count=imap_num_msg($mailBox);
        try {
            $markAsSeen = true;
            $dataEmail=[];
            //$mail = $mailbox->getMailboxInfo();
            $dataEmail["Unread"]=$count;
            imap_close($mailBox);
            return $dataEmail;  

        } catch(\ErrorException $ex) {
                $error = array("error"=>$ex->getMessage());
                return $dataEmail;  
    
        } catch(\PhpImap\Exceptions\ConnectionException $ex) {
            $error= array("error"=>"IMAP connection failed: " . implode(",", $ex->getErrors('all')));
            return $error;  
        }

    }

                /**
        * @Route("/api/imap/mailboxes/mail/pagined", methods={"POST"})
        * @OA\Post(
         * summary="Email pagined",
         * description="Email pagined",
         * operationId="emailpagined",
         * tags={"Imap"},
         * @OA\RequestBody(
         *    required=true,
         *    description="parametro",
         *    @OA\JsonContent(
         *       required={"page"},
         *       @OA\Property(property="page", type="integer", format="integer", example="1"),
         *       @OA\Property(property="rowByPage", type="integer", format="integer", example="1"),
         *       @OA\Property(property="buzonId", type="integer", format="integer", example="null"),
         *       @OA\Property(property="sort", type="array", @OA\Items(type="array",@OA\Items()), example={{"direction":"desc"},{"by":"arrival"}}),
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

    public function listMessages(Request $request,CuentaEmailRepository $repository) {
        $param = json_decode($request->getContent(),true);
        $mailBox=$this->getEmailConnect($param["buzonId"],$repository);
       // $mailBox2=$this->getEmailConnectLibrary(1,$repository);

        //        $mailBoxLibrary=$this->getEmailConnectLibrary($param["buzonId"],$repository);

        if(!$mailBox){
            return new JsonResponse(array("error"=>"conexion no establecida,credenciales invalidas"),409);            
        }
        $limit = ($param["rowByPage"] * $param["page"]);
        $start = ($limit - $param["rowByPage"]) + 1;
        $start = ($start < 1) ? 1 : $start;
        $limit = (($limit - $start) != ($param["rowByPage"]-1)) ? ($start + ($param["rowByPage"]-1)) : $limit;
        $return=array();


        if(true === is_array($param["sort"])) {
            $sorting = array(
                        'direction' => array(   'asc' => 0, 
                                                'desc' => 1),
 
                        'by'        => array(   'date' => SORTDATE,
                                                'arrival' => SORTARRIVAL,
                                                'from' => SORTFROM,
                                                'subject' => SORTSUBJECT,
                                                'size' => SORTSIZE));
            $by = (true === is_int($by = $sorting['by'][$param["sort"][1]["by"]])) 
                            ? $by 
                            : $sorting['by']['date'];
            $direction = (true === is_int($direction = $sorting['direction'][$param["sort"][0]["direction"]])) 
                            ? $direction 
                            : $sorting['direction']['desc'];
 
            $sorted = imap_sort($mailBox , $by, $direction);
 
            $msgs = array_chunk($sorted, $param["rowByPage"]);
            $msgs = $msgs[$param["page"]-1];
        }
        else 
            $msgs = range($start, $limit); //just to keep it consistent
        
            $result = imap_fetch_overview($mailBox, implode(',', $msgs), 0);
        foreach($result as $clave){
    
            $texto=imap_mime_header_decode($clave->subject);
            $subject="";
            foreach($texto as $c){ 
                $subject=$subject.$c->text ;     
            }


            $s = imap_fetchstructure($mailBox,$clave->msgno);
           $this->getAttachmend($s,$mailBox,51);
   
               
   
              // $mail=$mailBox2->getMail(4);
   
            $clave->subject=$subject;            
            $clave->hasAttanchend = $this->hasAttachmend; 

        }
        if(false === is_array($result)) return false;
 
        //sorting!
        if(true === is_array($param["sort"])) {
            $tmp_result = array();
            foreach($result as $r){
                $tmp_result[$r->msgno] = $r;

            }
            $result = array();
            foreach($msgs as $msgno) {
                $result[] = $tmp_result[$msgno];
            }
        }


        $return =$this->array_sort_by($result,"date",SORT_ASC);
        $return = array('data' => $result,
                        'start' => $start,
                        'limit' => $limit,
                        'total' => imap_num_msg($mailBox));
        $return['pages'] = ceil($return['total'] / $param["rowByPage"]);


        imap_close($mailBox);

        return new JsonResponse($return,200);
    }

    private function getEmailConnect($buzonId,CuentaEmailRepository $repository){
        $buzon = $repository->findOneBuzonByIdAndIdUser($buzonId,$this->security->getUser()->getId())[0];
        if(is_null($buzon->getTipoCuenta()["imap"]))
        return null;
        $imapServer= "{". $buzon->getTipoCuenta()["imap"]."}";  
        $password=$buzon->getPassword();
        $email=$buzon->getEmail();
       
        $mailBox = @\imap_open($imapServer, $email, $password, 0, 0);       
        var_dump($imapServer, $email, $password);
        var_dump(imap_errors());

        return $mailBox;
          
    }


    private function getEmailConnectLibrary($buzonId,CuentaEmailRepository $repository){
        $buzon = $repository->findOneBuzonByIdAndIdUser($buzonId,$this->security->getUser()->getId())[0];
        if(is_null($buzon->getTipoCuenta()["imap"]))
        return null;
        $imapServer= "{". $buzon->getTipoCuenta()["imap"]."}";  
        $password=$buzon->getPassword();
        $email=$buzon->getEmail();
        $mailBox = new \PhpImap\Mailbox(
            "$imapServer", // IMAP server and mailbox folder
            "$email", // Username for the before configured mailbox
            "$password", // Password for the before configured username,
            null,
            'UTF-8', // Server encoding (optional)
            true, // Trim leading/ending whitespaces of IMAP path (optional)
            false // Attachment filename mode (optional; false = random filename; true = original filename)
        );        


            return $mailBox;
          
    }

    private function getAttachmend($structure,$mailBox, $nro){
        if(isset($structure->parts) && count($structure->parts)) {
            $this->hasAttachmend=false;
            for($i = 0; $i < count($structure->parts); $i++) {

                $this->attachments[$i] = array(
                    'is_attachment' => false,
                    'filename' => '',
                    'name' => '',
                    'attachment' => ''
                );
                
                if($structure->parts[$i]->ifdparameters) {
                    foreach($structure->parts[$i]->dparameters as $object) {
                        if(strtolower($object->attribute) == 'filename') {
                            $this->attachments[$i]['is_attachment'] = true;
                            $this->attachments[$i]['filename'] = $object->value;
                            $this->hasAttachmend=true;
                        }
                    }
                }
                
                if($structure->parts[$i]->ifparameters) {
                    foreach($structure->parts[$i]->parameters as $object) {
                        if(strtolower($object->attribute) == 'name') {
                            $this->attachments[$i]['is_attachment'] = true;
                            $this->attachments[$i]['name'] = $object->value;
                            $this->hasAttachmend=true;
                        }
                    }
                }
                
                if($this->attachments[$i]['is_attachment']) {
                    $this->attachments[$i]['attachment'] = imap_fetchbody($mailBox, $nro, $i+1);
                    if($structure->parts[$i]->encoding == 3) { // 3 = BASE64
                     //   $this->attachments[$i]['attachment'] = base64_decode($this->attachments[$i]['attachment']);
                    }
                    elseif($structure->parts[$i]->encoding == 4) { // 4 = QUOTED-PRINTABLE
                      //  $this->attachments[$i]['attachment'] = quoted_printable_decode($this->attachments[$i]['attachment']);
                    }
                }
            }
}
    }        


    function array_sort_by(&$arrIni, $col, $order = SORT_ASC)


    {
        $arrAux = array();
        foreach ($arrIni as $key=> $row)
        {
            $arrAux[$key] = is_object($row) ? $arrAux[$key] = $row->$col : $row[$col];
            $arrAux[$key] = strtolower($arrAux[$key]);
        }
        return array_multisort($arrAux, $order, $arrIni);
    }    


}
