<?php
namespace App\Service;

class Correo
{
    private $correodestino;
    private $htmlcuerp;
    public function __construct()
    {
        
    }
 
    public function enviocorreo($correodestino,$htmlcuerp){
        $destinatario = $correodestino["email"]; 
            $asunto = "Notificaciones del Sistema GIEP"; 
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
            $headers .= "From: Dempre <admingiep@pafar.com.ve>\r\n"; 
            //direcciones que recibirán copia oculta 
            //$headers .= "Bcc: sirjcbg1@gmail.com\r\n"; 
            mail($destinatario,$asunto,$cuerpo,$headers); 

    } 

    public function enviocorreoparfar($correodestino,$htmlcuerp){
            $destinatario = $correodestino["email"]; 
            $asunto = $correodestino["asunto"];
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
            $headers .= "From: ". $correodestino["nombre"] ." <admingiep@pafar.com.ve>\r\n"; 
            //direcciones que recibirán copia oculta 
            //$headers .= "Bcc: sirjcbg1@gmail.com\r\n"; 
            mail($destinatario,$asunto,$cuerpo,$headers); 

    } 
    
    
    public function enviocorreo_qr($correodestino,$htmlcuerp,$asunto){
        $destinatario = $correodestino["email"]; 
            //$asunto = utf8_decode("Nos vemos este lunes en la Simón Bolívar"); 
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
            //$headers = "MIME-Version: 1.0\r\n"; 
            //$headers .= "Content-type: text/html; charset=iso-8859-1\r\n"; 

            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: multipart/mixed; boundary=\"boundary\"\r\n";

            //dirección del remitente 
            $headers .= "From: Dempre <admingiep@pafar.com.ve>\r\n"; 
            //direcciones que recibirán copia oculta 
            //$headers .= "Bcc: sirjcbg1@gmail.com\r\n";
          
            //$file = "http://bofficegiepstage.pafar.com.ve/public/dowload/IMG-20240301-WA0090.jpg";
            //$file = "http://bofficegiepstage.pafar.com.ve/public/dowload/qrcorreo.png";
            $file = "http://bofficegiepstage.pafar.com.ve/public/qrreportes.png";

            $mensaje = "--boundary\r\n";
            $mensaje .= "Content-Type: text/html; charset='utf-8'";
            $mensaje .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
            $mensaje .= "$cuerpo\r\n\r\n";
            $mensaje .= "--boundary\r\n";
            $mensaje .= "Content-Type: image/png; name=\"qrreportes.png\"\r\n";
            $mensaje .= "Content-Transfer-Encoding: base64\r\n";
            $mensaje .= "Content-Disposition: attachment; filename=\"qrreportes.png\"\r\n\r\n";
            $mensaje .= chunk_split(base64_encode(file_get_contents($file))) . "\r\n";
            $mensaje .= "--boundary--";

            mail($destinatario,$asunto,$mensaje,$headers); 

    } 

    public function enviocorreo_calendar($correodestino,$htmlcuerp,$asunto){
        $destinatario = $correodestino["email"]; 
            //$asunto = utf8_decode("Nos vemos este lunes en la Simón Bolívar"); 
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
            //$headers = "MIME-Version: 1.0\r\n"; 
            //$headers .= "Content-type: text/html; charset=iso-8859-1\r\n"; 

            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: multipart/mixed; boundary=\"boundary\"\r\n";

            //dirección del remitente 
            $headers .= "From: Dempre <admingiep@pafar.com.ve>\r\n"; 
            //direcciones que recibirán copia oculta 
            //$headers .= "Bcc: sirjcbg1@gmail.com\r\n";
          
            //$file = "http://bofficegiepstage.pafar.com.ve/public/dowload/IMG-20240301-WA0090.jpg";

            //$file = "http://bofficegiepstage.pafar.com.ve/public/qrreportes.png";

            $mensaje = "--boundary\r\n";
            $mensaje .= "Content-Type: text/html; charset='utf-8'";
            $mensaje .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
            $mensaje .= "$cuerpo\r\n\r\n";
            $mensaje .= "--boundary\r\n";
            //$mensaje .= "Content-Type: image/png; name=\"qrreportes.png\"\r\n";
            //$mensaje .= "Content-Transfer-Encoding: base64\r\n";
            //$mensaje .= "Content-Disposition: attachment; filename=\"qrreportes.png\"\r\n\r\n";
            //$mensaje .= chunk_split(base64_encode(file_get_contents($file))) . "\r\n";
            $mensaje .= "--boundary--";

            mail($destinatario,$asunto,$mensaje,$headers); 

    } 
    
    public function enviocorreoparfarcontactame($correodestino,$htmlcuerp){
        $destinatario = "soporte@pafar.net"; 
        $asunto = $correodestino["asunto"];
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
        //$headers .= "From: ". $correodestino["nombre"] ." <mariano@pafar.com.ve>\r\n"; 
        $headers .= "From: ". $correodestino["nombre"] ." <admingiep@pafar.com.ve>\r\n"; 

        //direcciones que recibirán copia oculta 
        //$headers .= "Bcc: sirjcbg1@gmail.com\r\n"; 
        mail($destinatario,$asunto,$cuerpo,$headers); 

  } 
    
}