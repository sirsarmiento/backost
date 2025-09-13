<?php
namespace App\Service;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\ValidationException;

class CodigoQR
{
    public function __construct()
    {
        
    }
 
    public function GenerarCodigo($string){
 
            $writer = new PngWriter();

            // Create QR code
            $qrCode = QrCode::create($string)
                ->setEncoding(new Encoding('UTF-8'))
                ->setErrorCorrectionLevel(new ErrorCorrectionLevelLow())
                ->setSize(300)
                ->setMargin(10)
                ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin())
                ->setForegroundColor(new Color(0, 0, 0))
                ->setBackgroundColor(new Color(255, 255, 255));

            // Create generic logo
            $logo = Logo::create('C:\xampp\htdocs\giep\public\images\avatar_femenino.png')
                ->setResizeToWidth(50);

            // Create generic label
            $label = Label::create('Label')
                ->setTextColor(new Color(255, 0, 0));

            $result = $writer->write($qrCode, $logo, $label);

            // Validate the result
            $writer->validateResult($result, 'Life is too short to be generating QR codes');
    } 

}