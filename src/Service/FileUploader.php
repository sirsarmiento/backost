<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\File;

class FileUploader
{
    private $targetDirectory;
    private $slugger;

    public function __construct($targetDirectory, SluggerInterface $slugger)
    {
        $this->targetDirectory = $targetDirectory;
        $this->slugger = $slugger;
    }

    public function upload(UploadedFile $file,$carpeta="")
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        try {
            if($carpeta!=""){
                //$path = $this->getTargetDirectory().$carpeta;
                $path = $this->getTargetDirectory()."/".$carpeta;

                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }
            }else{
                $path=$this->getTargetDirectory();
            }  
            $file->move($path, $fileName);
        } catch (FileException $e) {
            // ... handle exception if something happens during file upload
        }

        return $carpeta."/".$fileName;
        //return $fileName;
    }

    public function uploaddocument(UploadedFile $file,$carpeta="",$nemotecnico)
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        //$fileName = $safeFilename.'-'.$nemotecnico.'.'.$file->guessExtension();
        //$fileName = $safeFilename.'-'.$nemotecnico.'.'.$file->getClientOriginalExtension();
        $fileName = $nemotecnico.'.'.$file->getClientOriginalExtension();

        try {
            if($carpeta!=""){
                $path = $this->getTargetDirectory().$carpeta;
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }
            }else{
                $path=$this->getTargetDirectory();
            }    
            $file->move($path, $fileName);
        } catch (FileException $e) {
            return false;
            // ... handle exception if something happens during file upload
        }
        return $carpeta."/".$fileName;
    }

    public function uploadwritedocument(UploadedFile $file,$carpeta="",$nemotecnico)
    {
        //$originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        //$safeFilename = $this->slugger->slug($originalFilename);
        //$fileName = $safeFilename.'-'.$nemotecnico.'.'.$file->guessExtension();
        try {
            if($carpeta!=""){
                $path = $this->getTargetDirectory().$carpeta;
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                    rmdir($path);
                }else{
                    rmdir($path);
                    mkdir($path, 0777, true);
                    rmdir($path);    
                }
            }else{
                rmdir($path);
                mkdir($path, 0777, true);
                rmdir($path);
            }    
        } catch (FileException $e) {
            return false;
            // ... handle exception if something happens during file upload
        }

        return true;
    }

    public function download($fileName,$nomdir,$carpeta="")
    {
    /* public function download(UploadedFile $file,$carpeta="")
    { */
        //$originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        //$safeFilename = $this->slugger->slug($originalFilename);
        //$fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();
        //$fileName = $safeFilename.'.'.$file->guessExtension();

        try {
            if($carpeta!=""){
                $path = $this->getTargetDirectory().$carpeta;
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }
            }else{
                $path=$this->getTargetDirectory();
            }    
            //$file->move($path, $fileName);
            $resp = $this->createZipFromFolder($path,'../public/dowload',$fileName,$nomdir);
             
            return $resp;



        } catch (FileException $e) {
            // ... handle exception if something happens during file upload
        }

        return $carpeta."/".$fileName;
    }

    public function createZipFromFolder($pathToStart, $targetPath, $targetFileName,$nomdir)
        {
            $this->borrar_directorio($targetPath);
 
            $arrayarch=explode(".", $targetFileName); 
            $carpt = $targetPath.'/'. $nomdir;
            $carpeta1 = glob($carpt.'/*'); //Definimos la primera ruta
            //Recorremos todos los archivos que estan dentro de la primera carpeta
            if(file_exists($carpt)){
                foreach($carpeta1 as $archivo){
                    if(is_file($archivo))      // Comprobamos que sean ficheros normales, y de ser asi los eliminamos en la siguiente linea
                    unlink($archivo);          //Eliminamos el archivo

                }
                unlink($carpt.'/.ORIG_HEAD.txt');          //Eliminamos el archivo 
            }else{
               //if(!file_exists($targetPath.'/'. $nomdir)) mkdir($targetPath.'/'. $nomdir, 0777, true);
               mkdir($targetPath.'/'. $nomdir, 0777, true);
            }

            //$zip_name = ('documentos.zip');
            $zip_name = ($arrayarch[0].'.zip');
            $path_zip = ($targetPath.'/'.$arrayarch[0].'/'.$zip_name);

            $this->pfn = '';
            $this->pathToStart = $pathToStart;
            $zip = new \ZipArchive();
            if(!file_exists($targetPath)) mkdir($targetPath);
            if ($zip->open($path_zip, \ZipArchive::CREATE) === true)
            {
                copy($pathToStart.'/archivos/giep/documentos/'.$targetFileName, $targetPath.'/'.$nomdir.'/'.$nomdir.'.'.$arrayarch[1]);
                $fh = fopen($targetPath.'/'. $nomdir .'/'. ".ORIG_HEAD.txt", 'w') or die("Se produjo un error al crear el archivo");
                $texto = $arrayarch[0];
                fwrite($fh, $texto) or die("No se pudo escribir en el archivo");
                fclose($fh);
                 //Creamos el archivo
                $zip1 = new \ZipArchive();

                //abrimos el archivo y lo preparamos para agregarle archivos
                
                if(is_file($targetPath."/". $nomdir .".zip")) {
                    unlink($targetPath."/". $nomdir .".zip");
                }
                
                $zip1->open($targetPath."/". $nomdir .".zip", \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
                //$zip->addFile($pathToStart.'archivos/giep/documentos/'.$targetFileName,$targetFileName);

                //indicamos cual es la carpeta que se quiere comprimir
                $origen = realpath($targetPath);
                //Ahora usando funciones de recursividad vamos a explorar todo el directorio y a enlistar todos los archivos contenidos en la carpeta
                $files = new \RecursiveIteratorIterator(
                            new \RecursiveDirectoryIterator($origen),
                            \RecursiveIteratorIterator::LEAVES_ONLY
                );
                //Ahora recorremos el arreglo con los nombres los archivos y carpetas y se adjuntan en el zip
                foreach ($files as $name => $file)
                {
                if (!$file->isDir())
                {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($origen) + 1);

                    $zip1->addFile($filePath, $relativePath);
                }
                }

                //Se cierra el Zip
                $zip1->close();  
                //return $path_zip;
                $zip_name = $targetPath."/". $nomdir .".zip";
                return $zip_name;

                //$zip->addFile($pathToStart.'archivos/giep/documentos/'.$targetFileName,$targetFileName);
                 //$zip->addFile('../public/images/STORAGE1.txt','nemotecnico.txt');
                //$zip->close();
                /* if(!empty($zip_name) && file_exists($targetPath)){
                  //if(!empty($zip_name) && file_exists($path_zip)){

                    // Define headers
                    header("Cache-Control: public");
                    header("Content-Description: File Transfer");
                    header("Content-Disposition: attachment; filename=$zip_name");
                    header("Content-Type: application/zip");
                    header("Content-Transfer-Encoding: binary");
                    //return $path_zip;
                    return $zip_name;

                    //exit;
                }else{
                    echo 'The file does not exist.';
                }  */
            }
            else return false;
     }
     
     public function metadata_file($file,$nomfiles,$nomdirectory,$carpeta="") {
        $path = $this->getTargetDirectory();
        $targetPath="../public/metadata";
        $this->borrar_directorio($targetPath.'/'.$nomdirectory);
        $arrayarch=explode(".", $nomfiles); 
        $carpt = $targetPath.'/'. $nomdirectory;
        $carpeta1 = glob($carpt.'/*'); //Definimos la primera ruta
        //Recorremos todos los archivos que estan dentro de la primera carpeta
        if(file_exists($carpt)){
            foreach($carpeta1 as $archivo){
                if(is_file($archivo))      // Comprobamos que sean ficheros normales, y de ser asi los eliminamos en la siguiente linea
                unlink($archivo);          //Eliminamos el archivo
            }
        }else{
           mkdir($targetPath.'/'. $nomdirectory, 0777, true);
        }
        $brochureFileName = $this->uploaddocument($file,"/".$targetPath.'/'.$nomdirectory,$nomdirectory);   
       return $brochureFileName;
    }

    public function metadata_filef($dirfile1,$dirfile2,$carpeta="") {
        $imagen = false;
        $md5image2 = md5(file_get_contents($dirfile2));
        $md5image1 = md5(file_get_contents($dirfile1));
        if ($md5image1 == $md5image2) {
            $imagen = true;
        }else{
            $imagen = false;
        }
       return $imagen;
    }
     
     public function borrar_directorio($dirname) {
         $dir_handle='';
        //si es un directorio lo abro
             if (is_dir($dirname))
               $dir_handle = opendir($dirname);
            //si no es un directorio devuelvo false para avisar de que ha habido un error
         if (!$dir_handle)
              return false;
            //recorro el contenido del directorio fichero a fichero
         while($file = readdir($dir_handle)) {
               if ($file != "." && $file != "..") {
                       //si no es un directorio elemino el fichero con unlink()
                    if (!is_dir($dirname."/".$file))
                         unlink($dirname."/".$file);
                    else //si es un directorio hago la llamada recursiva con el nombre del directorio
                    $this->borrar_directorio($dirname.'/'.$file);
               }
         }
         closedir($dir_handle);
        //elimino el directorio que ya he vaciado
         rmdir($dirname);
         return true;
    }
   
    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}



