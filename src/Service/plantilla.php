<?php
namespace App\Service;
/**
 * Plantilla para encabezado y pie de página
*/
//require 'fpdf/fpdf.php';
use \fpdf;
use qrencode;
//use \phpqrcode;
class PDF extends FPDF{
    // Cabecera de página
    function Header($tituloReport,$logo,$logoqr)
    {
        global $nombreGrado;
        global $tituloReporte;
        $this->codqr = new qrencode();
        // Logo
        $this->Image("../public/images/".$logo, 10, 5, 25);
        // Arial bold 15
        $this->SetFont("Arial", "B", 12);
        // Título
        $this->Cell(25);
        $this->Cell(140, 5,  mb_convert_encoding($tituloReport, 'ISO-8859-1', 'UTF-8'), 0, 0, "C");
        $this->Cell($this->Image($logoqr, 195, 5, 10));
        $this->SetFont("Arial", "", 9);
        $this->Cell(-30, 20, "Fecha: " . date("d/m/Y"), 0, 1, "C");
        // Salto de línea
        $this->Ln(10);
    }

    // Pie de página
    function Footer()
    {
        // Posición: a 1,5 cm del final
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial', 'I', 8);
          // Logo QR
          //$this->Image("../public/images/".$logoqr, 10, 5, 13);
          // Arial bold 15
        // Número de página
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}
