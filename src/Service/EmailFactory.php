<?php
namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
class EmailFactory
{
    private  $html;
    private  $toAddress=[];
    private  $toCc=[];
    private  $subject;
    private $emailfrom;
    private $emailer;
    public function __construct($emailfrom,MailerInterface $emailer)
    {
        $this->emailer=$emailer;
        $this->emailfrom=$emailfrom;
    }
 
    public function setHtml($html){
        $this->html=$html;
    }

    public function setTo($address){

        if(count($address)>1){
            foreach($address as $clave=>$valor){                 
                $this->toAddress[] = new Address($valor["email"]);
           }   
        }else{
            $this->toAddress[]=$address["email"];
        }
    }

    public function setSubject($subject){
            $this->subject = $subject;
    }

    public function setCc($address){
        if(count($address)>1){
            foreach($address as $value){
                $this->toCc[] = new Address($value);
            }
        }else{
            $this->toCc[]=$address["email"];
        }

    }

    public function sendMail(){
       foreach($this->toAddress as $valor){    
            $email = (new Email())
                ->from($this->emailfrom)
                ->to($valor)
                ->cc('cc@example.com')
                //->bcc('bcc@example.com')
                //->replyTo('fabien@example.com')
                //->priority(Email::PRIORITY_HIGH)
                ->subject($this->subject)
                ->text('Sending emails is fun again!')
                ->html($this->html);
                $this->emailer->send($email);
                sleep(1);
        }
        foreach($this->toCc as $valor){    
            $email = (new Email())
                ->from($this->emailfrom)
                ->to($valor)
                ->cc('cc@example.com')
                //->bcc('bcc@example.com')
                //->replyTo('fabien@example.com')
                //->priority(Email::PRIORITY_HIGH)
                ->subject($this->subject)
                ->text('Sending emails is fun again!')
                ->html($this->html);
                $this->emailer->send($email);
                sleep(1);
        }    
    }

    
}