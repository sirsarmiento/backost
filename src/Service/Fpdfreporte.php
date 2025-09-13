<?php
namespace App\Service;
use plantilla;
use qrencode;
use qrimage;
use qrinput;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
class Fpdfreporte
{
    public $pdf;
    public $codqr;
    private $params;
    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    public function pushCod_QR($nonmarchivo)
    {
       $this->codqr = new qrencode();
       $this->codqr->png('https://bofficegiepstage.pafar.com.ve/public/account/tokenpdf/validador/'.$nonmarchivo,"qrreportes.png");
       $logoqr = "../public/qrreportes.png";
       return $logoqr;
    }

    public function pushCabecera_Pie_Repotes($tituloReporte,$logo,$logoqr,$nonmarchivo)
    {
       $this->codqr = new qrencode();
       //$this->codqr->SetWidths(15);

       $this->pdf = new PDF("P", "mm", "legal");
       $this->pdf->SetAuthor('PAFAR');
       $this->pdf->AliasNbPages();
       $this->pdf->SetMargins(10, 10, 10);
       $this->pdf->SetAutoPageBreak(true, 20); //salto de pagina automatico
       $this->codqr->png('https://bofficegiepstage.pafar.com.ve/public/account/tokenpdf/validador/'.$nonmarchivo,"qrreportes.png");
       $logoqr = "../public/qrreportes.png";
       $this->pdf->AddPage('','', 0,$tituloReporte,$logo,$logoqr);
    }

    //public function pushEncabezadoTablasRepotes($arrtbcabecera,$arrdata)
    public function pushEncabezadoTablasRepotes($arrtbcabecera,$arrtbanchoceldas,$arrtbaltoceldas,$titulotb,$tituloReporte,$logo,$logoqr)
    {
        $param = json_encode($arrtbcabecera,true);
         //************************************************************************** */
		// -----------ENCABEZADO TABLA------------------
        $height_of_cell = 1; // mm
        //$page_height = 286.93; // mm (portrait letter)
        $page_height = 312.10; // mm (portrait letter)
        $bottom_margin = 0; // mm
          for($i=0;$i<=100;$i++) :
            $block=floor($i/6);
            $space_left=$page_height-($this->pdf->GetY()+$bottom_margin); // space left on page
              if ($i/6==floor($i/6) && $height_of_cell > $space_left) {
                $this->pdf->AddPage('','', 0,$tituloReporte,$logo,$logoqr); // page break
              }
          endfor;

        $this->pdf->SetFont('Helvetica', 'B', 8);
        $this->pdf->Cell(200,10, utf8_decode($titulotb),0, 0 , 'C');
        // Salto de línea
        $this->pdf->Ln(8);

		$this->pdf->SetX(15);
		$this->pdf->SetFont('Helvetica', 'B', 8);
        for ($i = 0; $i < count($arrtbcabecera); $i++) {
            $dime = $arrtbcabecera[$i];
            $this->pdf->Cell($arrtbanchoceldas[$i], $arrtbaltoceldas[$i], utf8_decode($arrtbcabecera[$i]), 1, 0, 'C', 0);
		}
        // Salto de línea
        $this->pdf->Ln(8);
		// -------TERMINA----ENCABEZADO------------------
		$this->pdf->SetFillColor(255, 255, 255); //color de fondo rgb
		$this->pdf->SetDrawColor(61, 61, 61); //color de linea  rgb
		$this->pdf->SetFont('Arial', '', 8);
        //El ancho de las celdas
        $this->pdf->SetWidths($arrtbanchoceldas); //???
		//$this->pdf->SetWidths(array(10, 60, 80, 35)); //???
		//la alineación de cada COLUMNA!!!
		//$this->pdf->SetAligns(array('C','C','C','L'));
    }

    public function pushDataTablasRepotes($arrdata,$tituloReporte,$logo,$logoqr,$arrtbcabecera,$titulotb,$arrtbanchoceldas,$arrtbaltoceldas,$nonmarchivo)
    {
        $param = json_encode($arrdata,true);
		//for ($i = 0; $i < count($arrdata); $i++) {
        //    $dime = $arrdata[$i];
            $this->pdf->Row($arrdata, 15,$tituloReporte,$logo,$logoqr,$arrtbcabecera,$titulotb,$arrtbanchoceldas,$arrtbaltoceldas,$nonmarchivo);
		//}
    }

    public function pushCierreRepotes($nonmarchivo)
    {
        /* $DateAndTime = date('his', time());
        $nonmarchivo = "Expedientes" .'-' .date("Y") . date("m") . date("d") . $DateAndTime; */
        $urles="../public/dowload/".$nonmarchivo.".pdf";
        $this->pdf->Output($urles,'F');
        $imgbinary = fread(fopen($urles, "r"), filesize($urles));
        $filetype = "pdf";
        $base64_arch = 'data:@file/'. $filetype .';base64,' . base64_encode($imgbinary);  
        return $dataDatosArchivo = array($base64_arch,$nonmarchivo,$filetype);
    }

    public function pushSalto()
    {
        // Salto de línea
        $this->pdf->Ln(5);
    }
    
    public function pushCod_QR_Correo($datosqr)
    {
       $this->codqr = new qrencode();
       $this->codqr->png($datosqr,"qrreportes.png");
       $logoqr = "../public/qrreportes.png";
       //$logoqr = "http://bofficegiepstage.pafar.com.ve/public/dowload/qrcorreo.png";
       
       return $logoqr;
    }
}