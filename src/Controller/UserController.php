<?php

namespace App\Controller;

use App\Entity\User;
use App\Dto\UserOutPutDto;
use App\Dto\MenuOutPutDto;
use App\Dto\CorreoSubjectOutPutDto;
use App\Entity\Token;
use App\Repository\UserRepository;
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
use  App\Service\FileUploader;
use  App\Service\EmailFactory;
use  App\Service\Correo;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class UserController extends AbstractController
{
    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

        /**
        * @Route("/api/user/all", methods={"POST"})
        * @OA\Post(
         * summary="User All",
         * description="User All",
         * operationId="userall",
         * tags={"Users"},
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
    */    public function findAll(Request $request,UserRepository $repository): JsonResponse
    {
        $param = json_decode($request->getContent(),true);

        $data = $repository
        ->findAllPage($param,$this->params->get('urlapi'));
        if (!$data) {
            return new JsonResponse(['msg'=>'No existen Registros'],200);  
        }   
         return new JsonResponse($data,200);  
    }


        /**
         * @Route("/api/user/upload/photo", methods={"POST"})
         * @OA\Post(
         * summary="User upload Photo",
         * description="User upload Photo",
         * operationId="useruploadphoto",
         * tags={"Users"},
         *      @OA\RequestBody(
         *         @OA\MediaType(
         *             mediaType="multipart/form-data",
         *             @OA\Schema(
         *                 @OA\Property(
         *                     description="photo",
         *                     property="photo",
         *                     type="string",
         *                     format="binary",
         *                 ),
         *             )
         *         )
         *     ),
         * @OA\Response(
         *    response=422,
         *    description="Photo Incorrecta",
         *    @OA\JsonContent(
         *       @OA\Property(property="message", type="string", example="Sorry, wrong email address or password. Please try again")
         *        )
         *     )
         * )
    */ public function uploadPhoto(Request $request,ValidatorInterface $validator,FileUploader $fileUploader,UserRepository $repository): JsonResponse
    {
        $file = $request->files->get('photo');
        $em =$this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $repository = $this->getDoctrine()->getRepository(User::class);
        if ($file) {
            
            $brochureFileName = $fileUploader->upload($file,"fotos/".$user->getId());
            return $repository->putPhoto($brochureFileName,$validator); 
            
        }else{
            return new JsonResponse("{error:error cargando foto}",409);  
        }   


    }


        /**
         * @Route("/api/user/valid/user", methods={"POST"})
         * @OA\Post(
         * summary="User upload user",
         * description="User upload user",
         * operationId="uservalid",
         * tags={"Users"},
         *      @OA\RequestBody(
         *         @OA\MediaType(
         *             mediaType="multipart/form-data",
         *             @OA\Schema(
         *                 @OA\Property(
         *                     description="users",
         *                     property="users",
         *                     type="string",
         *                     format="binary",
         *                 ),
         *             )
         *         )
         *     ),
         * @OA\Response(
         *    response=422,
         *    description="Photo Incorrecta",
         *    @OA\JsonContent(
         *       @OA\Property(property="message", type="string", example="Sorry, wrong email address or password. Please try again")
         *        )
         *     )
         * )
    */ public function validUsers(Request $request,ValidatorInterface $validator,FileUploader $fileUploader,UserRepository $repository): JsonResponse
    {
        $file = $request->files->get('users');
        if($file->getMimeType()!="application/vnd.ms-excel"){
            return new JsonResponse(['error'=>'Formato de Archivo Incorrecto'],409);  
        }
        
        $em =$this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $repository = $this->getDoctrine()->getRepository(User::class);
        if ($file) {
            $brochureFileName = $fileUploader->upload($file,"/datauser");

            $brochureFileName= $this->getParameter('kernel.project_dir'). '/public'.$brochureFileName;
     
            return $repository->validUserData($brochureFileName,$validator); 
        }else{
            return new JsonResponse(['error'=>'Error Cargando Archivo'],409);  
        }   


    }



    /**
        * @Route("/api/user/valid/user/byemail", methods={"POST"})
        * @OA\Post(
         * summary="Valid User By Email",
         * description="Valid User By Email",
         * operationId="validuserbyemail",
         * tags={"By Email"},
         * @OA\RequestBody(
         *    required=true,
         *    description="By Email",
         *    @OA\JsonContent(
         *       required={"email"},
         *       @OA\Property(property="users", type="array", @OA\Items(type="array",@OA\Items()), example={"valeriebaez.trabajo@gmail.com","maylygibbs807@gmail.com"}),
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
    public function validaUsersbyEmail(Request $request): Response
    {   
        try {
            $data = json_decode($request->getContent(),true);
            $repository = $this->getDoctrine()->getRepository(User::class);
            return $repository->validaUsersbyEmail($data); 
        } catch (Exception $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],500);
        }
    }

    /**
     *  Get an user. 
     * @Route("/api/user/{id}", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Returns user",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=UserOutPutDto::class))
     *     )
     * )
     * @OA\Tag(name="Users")
     * @Security(name="Bearer")
     */
    public function findById($id,UserRepository $repository): JsonResponse
    {
        $data = $repository
        ->findById($id,$this->params->get('urlapi'));
        if (!$data) {
            return new JsonResponse(['msg'=>'No existen Registros'],404);  
        }   
         return new JsonResponse($data,200);  
    }


    /**
     *  Get an user. 
     * @Route("/api/user/info/detalle", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Returns user",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=UserOutPutDto::class))
     *     )
     * )
     * @OA\Tag(name="Users")
     * @Security(name="Bearer")
     */
    public function info(UserRepository $repository): JsonResponse
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $data = $repository
        ->findById($user->getId(),$this->params->get('urlapi'));
        if (!$data) {
            return new JsonResponse(['msg'=>'Usuario no existe'],200);  
        }   
         return new JsonResponse($data,200);  
    }


    /**
     *  Get an user. 
     * @Route("/api/user/menu/opciones", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Returns user",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=MenuOutPutDto::class))
     *     )
     * )
     * @OA\Tag(name="Users")
     * @Security(name="Bearer")
     */
    public function menu(UserRepository $repository): JsonResponse
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $data = $repository
        ->menu($user->getId());
        if (!$data) {
            return new JsonResponse(['msg'=>'No existen Registros'],200);  
        }   
         return new JsonResponse($data,200);  
    }


    /**
        * @Route("/api/user", methods={"POST"})
        * @OA\Post(
         * summary="Create User",
         * description="Create User",
         * operationId="create",
         * tags={"Users"},
         * @OA\RequestBody(
         *    required=true,
         *    description="Data User",
         *    @OA\JsonContent(
         *       required={"username","password"},
         *       @OA\Property(property="idStatus", type="integer", example=1),
         *       @OA\Property(property="numeroDocumento", type="string", example="numeroDocumento"),
         *       @OA\Property(property="tipoDocumentoIdentidad", type="integer", example="V"),
         *       @OA\Property(property="primerNombre", type="string", example="Petra"),
         *       @OA\Property(property="segundoNombre", type="string", example="Maria"),
         *       @OA\Property(property="primerApellido", type="string", example="Padron"),
         *       @OA\Property(property="segundoApellido", type="string", example="Fuentes"),
         *       @OA\Property(property="fechaNacimiento", type="datetime", example="2022-04-01"),
         *       @OA\Property(property="email", type="string", example="sss@gmail.com"),
         *       @OA\Property(property="idCargo", type="integer", example="2"),
         *       @OA\Property(property="idDependencia", type="integer", example="2"),
         *       @OA\Property(property="telefono", type="array", @OA\Items(type="array",@OA\Items()), example={{"numero":"0412345643"},{"numero":"0412345645"}}),
         *       @OA\Property(property="roles", type="array", @OA\Items(type="array",@OA\Items()), example={{"rol":"ROLE_ADMINISTRADOR"},{"rol":"ROLE_ANALISTA"}}),
         *       @OA\Property(property="sexo", type="string", example="Femenino"),
         *       @OA\Property(property="direccion", type="string", example="Av ppal Francisco Miranda"),
         *       @OA\Property(property="pais", type="integer", example="1"),
         *       @OA\Property(property="estado", type="integer", example="1"),
         *       @OA\Property(property="ciudad", type="integer", example="1"),
         *       @OA\Property(property="idGerencia", type="integer", example="1"),
         *       @OA\Property(property="idCoordinacion", type="integer", example="1")
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
    public function post(Request $request,ValidatorInterface $validator,Helper $helper,Correo $correo): Response
    {   
        try {
            $data = json_decode($request->getContent(),true);
            $repository = $this->getDoctrine()->getRepository(User::class);
            return $repository->post($data,$validator,$helper,$correo); 
        } catch (HttpException $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],500);
        }
    }


    /**
        * @Route("/api/user/{id}", methods={"PUT"})
        * @OA\Put(
         * summary="Put User",
         * description="Update User",
         * operationId="updateUser",
         * tags={"Users"},
         * @OA\RequestBody(
         *    required=true,
         *    description="Data User",
         *    @OA\JsonContent(
         *       required={"username","password"},
         *       @OA\Property(property="idStatus", type="integer", example=1),
         *       @OA\Property(property="numeroDocumento", type="string", example="numeroDocumento"),
         *       @OA\Property(property="tipoDocumentoIdentidad", type="integer", example="V"),
         *       @OA\Property(property="primerNombre", type="string", example="Petra"),
         *       @OA\Property(property="segundoNombre", type="string", example="Maria"),
         *       @OA\Property(property="primerApellido", type="string", example="Padron"),
         *       @OA\Property(property="segundoApellido", type="string", example="Fuentes"),
         *       @OA\Property(property="fechaNacimiento", type="datetime", example="2022-04-01"),
         *       @OA\Property(property="email", type="string", example="sss@gmail.com"),
         *       @OA\Property(property="idDependencia", type="integer", example="2"),
         *       @OA\Property(property="idCargo", type="integer", example="2"),
         *       @OA\Property(property="telefono", type="array", @OA\Items(type="array",@OA\Items()), example={{"numero":"0412345643"},{"numero":"0412345645"}}),
         *       @OA\Property(property="roles", type="array", @OA\Items(type="array",@OA\Items()), example={{"rol":"ROLE_ADMINISTRADOR"},{"rol":"ROLE_ANALISTA"}}),
         *       @OA\Property(property="sexo", type="string", example="Femenino"),
         *       @OA\Property(property="direccion", type="string", example="Av ppal Francisco Miranda"),
         *       @OA\Property(property="pais", type="integer", example="1"),
         *       @OA\Property(property="estado", type="integer", example="1"),
         *       @OA\Property(property="ciudad", type="integer", example="1"),
         *       @OA\Property(property="idGerencia", type="integer", example="1"),
         *       @OA\Property(property="idCoordinacion", type="integer", example="1")
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
    public function put($id,Request $request,ValidatorInterface $validator,Helper $helper): Response
    {
        try {
            $data = json_decode($request->getContent(),true);
            $em =$this->getDoctrine()->getManager();
            $repository = $this->getDoctrine()->getRepository(User::class);
            return $repository->put($data,$id,$validator,$helper); 
        } catch (HttpException $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],500);
        }
    }

     /**
        * @Route("/api/user/perfil/{id}", methods={"PUT"})
        * @OA\Put(
         * summary="Editar Perfil User",
         * description="Editar Perfil User",
         * operationId="EditarPerfilUser",
         * tags={"Users"},
         * @OA\RequestBody(
         *    required=true,
         *    description="Data User",
         *    @OA\JsonContent(
         *       @OA\Property(property="redes", type="array", @OA\Items(type="array",@OA\Items()), example={{"tipo":"1","red":"sirface@facebook.com"}}),
         *       @OA\Property(property="direccion", type="string", example="Av ppal Francisco Miranda"),
         *       @OA\Property(property="telefono", type="array", @OA\Items(type="array",@OA\Items()), example={{"numero":"04125235687"}}),
         * 
         *       @OA\Property(property="pais", type="integer", example="1"),
         *       @OA\Property(property="estado", type="integer", example="1"),
         *       @OA\Property(property="ciudad", type="integer", example="1")
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
    public function editarPerfil($id,Request $request,ValidatorInterface $validator,Helper $helper): Response
    {
        try {
            $data = json_decode($request->getContent(),true);
            $em =$this->getDoctrine()->getManager();
            $repository = $this->getDoctrine()->getRepository(User::class);
            return $repository->editarPerfil($data,$id,$validator,$helper); 
        } catch (HttpException $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],500);
        }
    }
    


        /**
        * @Route("/api/user/{id}", methods={"DELETE"})
        * @OA\Delete(
         * summary="Delete User",
         * description="Delete User",
         * operationId="deleteuser",
         * tags={"Users"},
         * @OA\Response(
         *    response=422,
         *    description="Wrong credentials response",
         *    @OA\JsonContent(
         *       @OA\Property(property="message", type="string", example="Sorry, wrong email address or password. Please try again")
         *        )
         *     )
         * )
    */
    public function delete($id,ValidatorInterface $validator,Helper $helper): Response
    {
        try {
            $em =$this->getDoctrine()->getManager();
            $repository = $this->getDoctrine()->getRepository(User::class);
            return $repository->delete($id,$validator,$helper); 
        } catch (HttpException $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],500);
        }
    }



        /**
        * @Route("/account/recovery-password", methods={"POST"})
        * @OA\Post(
         * summary="Recover Email",
         * description="Recover Email",
         * operationId="recover",
         * tags={"Users"},
         * @OA\RequestBody(
         *    required=true,
         *    description="email",
         *    @OA\JsonContent(
         *       required={"email"},
         *       @OA\Property(property="email", type="string", format="string", example="baezgregoric@gmail.com"),
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
    public function recoverPass(Request $request,ValidatorInterface $validator,Helper $helper,EmailFactory $email,Correo $correo):JsonResponse
    {   
        try {
            $em =$this->getDoctrine()->getManager();
            $data = json_decode($request->getContent(),true);
            $user = $this->getDoctrine()->getRepository(User::class)->findBy(array('email' => $data["email"]));
            if($user==null){
                return new JsonResponse(['msg'=>'No existe el correo electronico'],404);
            }
            $repository = $this->getDoctrine()->getRepository(Token::class);
            $result= json_decode($repository->post($data,$validator,$helper,$user));
            if($result->msg=="Token Creado"){
                 $urlFront =$this->params->get('urlfrom');
                /*$email->setHtml("Para realizar el cambio de contraseña haga click en el siguiente enlace:<br><br>"."<a href='".$urlFront."'?token=".$result->token."'>Cambiar Contraseña</a>");
                $email->setSubject("Cambio de Contraseña del Sistema GIEP");
                $email->setTo($data);
                $email->sendMail(); */
                $correo->enviocorreo($data,"Para realizar el cambio de contraseña haga click en el siguiente enlace:<br><br>"."<a href='".$urlFront."auth/change-pass/".$result->token."'>Cambiar Contraseña</a>");

//              $this->enviocorreo($data,"Para realizar el cambio de contraseña haga click en el siguiente enlace:<br><br>"."<a href='".$urlFront."auth/change-pass/".$result->token."'>Cambiar Contraseña</a>");
                return new JsonResponse(['msg'=>'Correo para el cambio de Contraseña Enviado'],200);
            }else{
                return new JsonResponse($result);
            }
        } catch (HttpException $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],500);
        }
    }




    public function enviocorreo($correodestino,$htmlcuerp){
        $destinatario = $correodestino["email"]; 
            $asunto = "Credenciales de Acceso al Sistema GIEP"; 
            $cuerpo = ' 
            <html> 
            <head> 
            <title>Sistema GIEP</title> 
            </head> 
            <body> 
            <p> 
            <b>'. $htmlcuerp .'</b>.  
            </p> 
            </body> 
            </html> 
            '; 
            //para el envío en formato HTML 
            $headers = "MIME-Version: 1.0\r\n"; 
            $headers .= "Content-type: text/html; charset=iso-8859-1\r\n"; 
            //dirección del remitente 
            $headers .= "From: Mariano Baez <mariano@pafar.com.ve>\r\n"; 
            //direcciones que recibirán copia oculta 
            //$headers .= "Bcc: sirjcbg1@gmail.com\r\n"; 
            mail($destinatario,$asunto,$cuerpo,$headers); 

    }

    /**
     */
        /**
        * @Route("/user/changepassword", methods={"POST"})
        * @OA\Post(
         * summary="Change Password",
         * description="Change Password",
         * operationId="changepassword",
         * tags={"Users"},
         * @OA\RequestBody(
         *    required=true,
         *    description="email",
         *    @OA\JsonContent(
         *       required={"password"},
         *       @OA\Property(property="password", type="string", format="string", example="Choc203302*."),
         *       @OA\Property(property="token", type="string", format="string", example="XMWDDEEEEXXWWWW")
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
    public function changePassword(Request $request,ValidatorInterface $validator,Helper $helper): Response
    {
        try {
            $data = json_decode($request->getContent(),true);
            $em =$this->getDoctrine()->getManager();           
            $repository = $this->getDoctrine()->getRepository(User::class);
            return $repository->changePassword($data,$validator,$helper); 
        } catch (HttpException $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],500);
        }
    }


    /**
     */
        /**
        * @Route("/api/user/changepassword/perfil", methods={"POST"})
        * @OA\Post(
         * summary="Change Password Perfil",
         * description="Change Password Perfil",
         * operationId="changepasswordPerfil",
         * tags={"Users"},
         * @OA\RequestBody(
         *    required=true,
         *    description="password",
         *    @OA\JsonContent(
         *       required={"password"},
         *       @OA\Property(property="password", type="string", format="string", example="Choc203302*."),
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
    public function changePasswordPerfil(Request $request,ValidatorInterface $validator,Helper $helper): Response
    {
        try {
            $data = json_decode($request->getContent(),true);
            $em =$this->getDoctrine()->getManager();           
            $repository = $this->getDoctrine()->getRepository(User::class);
            return $repository->changePasswordPerfil($data,$validator,$helper); 
        } catch (HttpException $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],500);
        }
    }


    /**
     */
        /**
        * @Route("/api/user/changePasswordTem", methods={"POST"})
        * @OA\Post(
         * summary="Change changePasswordTem",
         * description="Change changePasswordTem",
         * operationId="changePasswordTem",
         * tags={"Users"},
         * @OA\RequestBody(
         *    required=true,
         *    description="email",
         *    @OA\JsonContent(
         *       required={"password"},
         *       @OA\Property(property="password", type="string", format="string", example="Choc203302*."),
         *       @OA\Property(property="id", type="integer", format="integer", example="1")
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
    public function changePasswordTem(Request $request,ValidatorInterface $validator,Helper $helper): Response
    {
        try {
            $data = json_decode($request->getContent(),true);
            $em =$this->getDoctrine()->getManager();           
            $repository = $this->getDoctrine()->getRepository(User::class);
            return $repository->changePasswordTemp($data,$validator,$helper); 
        } catch (HttpException $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],500);
        }
    }

    /**
     *  Get an user roles. 
     * @Route("/api/user/roles", methods={"POST"})
     * @OA\RequestBody(
         *    required=true,
         *    description="email",
         *    @OA\JsonContent(
         *       required={"roles"},
         *       @OA\Property(property="roles", type="string", format="string", example="role1|role2|role3"),
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
     * @OA\Tag(name="Users")
     * @Security(name="Bearer")
     */
    public function findUserByRol(Request $request,UserRepository $repository): JsonResponse
    {

        $data = json_decode($request->getContent(),true);
        $data = $repository
        ->findUserByRol($data);
        if (!$data) {
            return new JsonResponse(['msg'=>'No existen Registros'],200);  
        }   
         return new JsonResponse($data,200);  
    }

    /**
        * @Route("/account/envio-correo", methods={"POST"})

        * @OA\Post(

         * summary="Envio Email",

         * description="Envio Email",

         * operationId="envioemail",

         * tags={"Users"},

         * @OA\RequestBody(

         *    required=true,

         *    description="email",

         *    @OA\JsonContent(

         *       required={"email"},

         *       @OA\Property(property="email", type="string", format="string", example="jaileop@gmail.com"),
         *       @OA\Property(property="nombre", type="string", format="string", example="mayly"),
         *       @OA\Property(property="asunto", type="string", format="string", example="carta de parfar"),
         *       @OA\Property(property="telefono", type="string", format="string", example="04125950736"),
         *       @OA\Property(property="mensaje", type="string", format="string", example="el correo parfar"),

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

     public function enviocorreoPass(Request $request,ValidatorInterface $validator,Helper $helper,EmailFactory $email,Correo $correo):JsonResponse
    {   
        try {
            $em =$this->getDoctrine()->getManager();
            $data = json_decode($request->getContent(),true);
            if ($data) {
                 $urlFront =$this->params->get('urlfrom');
                 $correo->enviocorreoparfar($data,"Sr(a).: ".$data["nombre"]."<br><br>"."Email: ".$data["email"]."<br><br>"."Tlf.: ".$data["telefono"]."<br><br>"."Mensaje: ".$data["mensaje"] ."<br><br>"."Quedo atento(a). "."<br><br>"."Saludos. ");
                return new JsonResponse(['msg'=>'Correo Enviado'],200);
            }else{
                return new JsonResponse(['msg'=>'Error del Data'],500);
            }
        } catch (HttpException $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],500);
        }
    }
      
    
     /**
     *  Get an user. 
     * @Route("/api/user/info/list", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Returns user",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=UserOutPutDto::class))
     *     )
     * )
     * @OA\Tag(name="Users")
     * @Security(name="Bearer")
     */
    public function list(UserRepository $repository): JsonResponse
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $data = $repository
        ->findByList();
        if (!$data) {
            return new JsonResponse(['msg'=>'No existen Registros'],200);  
        }   
         return new JsonResponse($data,200);  
    }


     /**
     *  Get an user. 
     * @Route("/api/user/info/{ci}", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Returns user",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=UserOutPutDto::class))
     *     )
     * )
     * @OA\Tag(name="Users")
     * @Security(name="Bearer")
     */
    public function findByCi($ci,UserRepository $repository): JsonResponse
    {
        $data = $repository
        ->findByCi($ci,$this->params->get('urlapi'));
        if (!$data) {
            return new JsonResponse(['msg'=>'No existen Registros'],404);  
        }   
         return new JsonResponse($data,200);  
    }


       /**
         * @Route("/api/user/upload/user", methods={"POST"})
         * @OA\Post(
         * summary="User upload user",
         * description="User upload user",
         * operationId="userupload",
         * tags={"Users"},
         *      @OA\RequestBody(
         *         @OA\MediaType(
         *             mediaType="multipart/form-data",
         *             @OA\Schema(
         *                 @OA\Property(
         *                     description="users",
         *                     property="users",
         *                     type="string",
         *                     format="binary",
         *                 ),
         *             )
         *         )
         *     ),
         * @OA\Response(
         *    response=422,
         *    description="Photo Incorrecta",
         *    @OA\JsonContent(
         *       @OA\Property(property="message", type="string", example="Sorry, wrong email address or password. Please try again")
         *        )
         *     )
         * )
    */
    public function uploadUsers(Request $request,ValidatorInterface $validator,FileUploader $fileUploader,UserRepository $repository): JsonResponse
    {
        $file = $request->files->get('users');
        if($file->getMimeType()!="application/vnd.ms-excel"){
            return new JsonResponse(['error'=>'Formato de Archivo Incorrecto'],409);  
        }
        
        $em =$this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $repository = $this->getDoctrine()->getRepository(User::class);
        if ($file) {
            $brochureFileName = $fileUploader->upload($file,"/datauser");

            $brochureFileName= $this->getParameter('kernel.project_dir'). '/public'.$brochureFileName;
     
            return $repository->loadDataUser($brochureFileName,$validator); 
        }else{
            return new JsonResponse(['error'=>'Error Cargando Archivo'],409);  
        }   


    }


     /**
        * @Route("/api/user/nacimientos", methods={"POST"})
        * @OA\Post(
         * summary="User Nacimientos",
         * description="User Nacimientos",
         * operationId="usernacimientos",
         * tags={"Users"},
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
    */    public function findNacimientos(Request $request,UserRepository $repository): JsonResponse
    {
        $param = json_decode($request->getContent(),true);

        return $repository->findNacimientos($param,$this->params->get('urlapi'));
       
        /* if (!$data) {
            return new JsonResponse(['msg'=>'No existen Registros'],200);  
        }   
         return new JsonResponse($data,200);   */
    }



   /**
     *  Get an user status. 
     * @Route("/api/user/status/list", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Returns user",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=UserOutPutDto::class))
     *     )
     * )
     * @OA\Tag(name="Users")
     * @Security(name="Bearer")
     */
    public function listStatus(UserRepository $repository): JsonResponse
    {
        try {
            $data = $repository->findByListStatus();
            if ($data==2) {
                return new JsonResponse(['msg'=>'Usuario Inactivo'],401);
            }else{
                return new JsonResponse(['msg'=>'Usuario Activo'],200);
            }
        } catch (HttpException $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],500);
        }
    }
    
    /**
        * @Route("/api/user/empresa", methods={"POST"})
        * @OA\Post(
         * summary="Update User Empresa",
         * description="Update User Empresa",
         * operationId="updateuserempresa",
         * tags={"Users"},
         * @OA\RequestBody(
         *    required=true,
         *    description="Data User",
         *    @OA\JsonContent(
         *       required={"idempresa"},
         *       @OA\Property(property="idempresa", type="integer", example=1)
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
    public function updateuserempresa(Request $request,ValidatorInterface $validator,Helper $helper,Correo $correo): Response
    {   
        try {
            $data = json_decode($request->getContent(),true);
            $repository = $this->getDoctrine()->getRepository(User::class);
            return $repository->updateuserempresa($data,$validator,$helper,$correo); 
        } catch (HttpException $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],500);
        }
    }
    
    /**
        * @Route("/account/contactame", methods={"POST"})
        * @OA\Post(
         * summary="Envio Email Contactame",
         * description="Envio Email Contactame",
         * operationId="envioemailcontactame",
         * tags={"Users"},
         * @OA\RequestBody(
         *    required=true,
         *    description="email",
         *    @OA\JsonContent(
         *       required={"email"},
         *       @OA\Property(property="email", type="string", format="string", example="baezgregoric@gmail.com"),
         *       @OA\Property(property="nombre", type="string", format="string", example="Luis Mariano Baez G"),
         *       @OA\Property(property="asunto", type="string", format="string", example="carta de parfar"),
         *       @OA\Property(property="telefono", type="string", format="string", example="04125950736"),
         *       @OA\Property(property="mensaje", type="string", format="string", example="el correo parfar para soporte"),
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

    public function enviocorreoContactame(Request $request,ValidatorInterface $validator,Helper $helper,EmailFactory $email,Correo $correo):JsonResponse
    {   
        try {
            $em =$this->getDoctrine()->getManager();
            $data = json_decode($request->getContent(),true);
            if ($data) {
                 $urlFront =$this->params->get('urlfrom');
                 $correo->enviocorreoparfarcontactame($data,"Datos de contacto"."<br><br>"."Nombre y Apellido: ".utf8_decode($data["nombre"])."<br><br>"."Email: ".$data["email"]."<br><br>"."Tlf.: ".$data["telefono"]."<br><br>"."Mensaje: ".utf8_decode($data["mensaje"]) ."<br><br>"."Quedo atento(a). "."<br><br>"."Saludos. ");
                return new JsonResponse(['msg'=>'Correo Enviado'],200);
            }else{
                return new JsonResponse(['msg'=>'Error del Data'],500);
            }
        } catch (HttpException $e) {
            return new JsonResponse(['msg'=>'Error del Servidor'],500);
        }
    }
    
    /**
     *  Get an correo subject. 
     * @Route("/account/correosubject/list", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Returns user",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=CorreoSubjectOutPutDto::class))
     *     )
     * )
     * @OA\Tag(name="Users")
     */
    public function listSubject(UserRepository $repository): JsonResponse
    {
        $data = $repository
        ->findListSubject();
        if (!$data) {
            return new JsonResponse(['msg'=>'No existen Registros'],200);  
        }   
         return new JsonResponse($data,200);  
    }

    /**
    *  Get All Responsibles.
    * @Route("/api/user", methods={"GET"})
    * @OA\Post(
        * summary="User",
        * description="Lista todo",
        * operationId="AllUser",
        * tags={"Users"},
        * @OA\RequestBody(
        *    required=true,
        *    description="Consulta todos los usuarios",
        * ),
        * @OA\Response(
        *    response=422,
        *    description="Wrong credentials response",
        *    @OA\JsonContent(
        *       @OA\Property(property="message", type="string", example="Sorry, wrong email address or password. Please try again")
        *        )
        *     )
        * )
        * @OA\Tag(name="Users")
        * @Security(name="Bearer")
    */   
    public function findAllResponsibles(Request $request,UserRepository $repository): JsonResponse
    {
        $data = $repository->getall();
        return new JsonResponse($data, 200);
    }

}
