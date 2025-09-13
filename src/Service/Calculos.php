<?php
namespace App\Service;

class Calculos
{
    private $startDate;
    private $endDate;
    public function __construct()
    {
        
    }
 
    public function dias_pasados_sin_fin_semana($fecha_inicial,$fecha_final){
        $fechaInicio=strtotime($fecha_inicial);
        $fechaFin=strtotime($fecha_final);
        $restadia=0;
        for($i=$fechaInicio; $i<=$fechaFin; $i+=86400){
            $fechaes = date("d-m-Y", $i);
            $dia=date("w", strtotime($fechaes));
            if($dia=="0" or $dia=="6"){
                $restadia++;
            }
        }
        $dias = (strtotime($fecha_inicial)-strtotime($fecha_final))/86400;
        $dias = abs($dias); $dias = floor($dias);
        $dias++;
        if($restadia>0){
            $dias = ($dias  -  $restadia);
        }
        return $dias;
    } 
}