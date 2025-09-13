<?php

namespace App\Service;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

class Helper{
    
    public $manager;
    public $validator;
    public $entitySpaceName;

    public function __construct($manager, $validator) {
        $this->manager = $manager;
        $this->validator = $validator;
        $this->entitySpaceName = 'App\Entity\\';
    }

    // Manage returns

    public function setEntitiManager($entitiManager){
        $this->manager = $entitiManager; 
    }


    private function getHttpStatusCode($code){ // https://en.wikipedia.org/wiki/List_of_HTTP_status_codes

        switch ($code) {
            case 511:
                return Response::HTTP_NETWORK_AUTHENTICATION_REQUIRED;
            case 510:
                return Response::HTTP_NOT_EXTENDED;
            case 508:
                return Response::HTTP_LOOP_DETECTED;
            case 507:
                return Response::HTTP_INSUFFICIENT_STORAGE;
            case 506:
                return Response::HTTP_VARIANT_ALSO_NEGOTIATES;
            case 505:
                return Response::HTTP_VERSION_NOT_SUPPORTED;
            case 504:
                return Response::HTTP_GATEWAY_TIMEOUT;
            case 503:
                return Response::HTTP_SERVICE_UNAVAILABLE;
            case 502:
                return Response::HTTP_BAD_GATEWAY;
            case 501:
                return Response::HTTP_NOT_IMPLEMENTED;
            case 500:
                return Response::HTTP_INTERNAL_SERVER_ERROR;
            case 451:
                return Response::HTTP_UNAVAILABLE_FOR_LEGAL_REASONS;
            case 431:
                return Response::HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE;
            case 429:
                return Response::HTTP_TOO_MANY_REQUESTS;
            case 428:
                return Response::HTTP_PRECONDITION_REQUIRED;
            case 426:
                return Response::HTTP_UPGRADE_REQUIRED;
            case 425:
                return Response::HTTP_TOO_EARLY;
            case 424:
                return Response::HTTP_FAILED_DEPENDENCY;
            case 423:
                return Response::HTTP_LOCKED;
            case 422:
                return Response::HTTP_UNPROCESSABLE_ENTITY;
            case 421:
                return Response::HTTP_MISDIRECTED_REQUEST;
            case 417:
                return Response::HTTP_EXPECTATION_FAILED;
            case 416:
                return Response::HTTP_RANGE_NOT_SATISFIABLE;
            case 415:
                return Response::HTTP_UNSUPPORTED_MEDIA_TYPE;
            case 414:
                return Response::HTTP_URI_TOO_LONG;
            case 413:
                return Response::HTTP_PAYLOAD_TOO_LARGE;
            case 412:
                return Response::HTTP_PRECONDITION_FAILED;
            case 411:
                return Response::HTTP_LENGTH_REQUIRED;
            case 410:
                return Response::HTTP_GONE;
            case 409:
                return Response::HTTP_CONFLICT;
            case 408:
                return Response::HTTP_REQUEST_TIMEOUT;
            case 407:
                return Response::HTTP_PROXY_AUTHENTICATION_REQUIRED;
            case 406:
                return Response::HTTP_NOT_ACCEPTABLE;
            case 405:
                return Response::HTTP_METHOD_NOT_ALLOWED;
            case 404:
                return Response::HTTP_NOT_FOUND;
            case 403:
                return Response::HTTP_FORBIDDEN;
            case 402:
                return Response::HTTP_PAYMENT_REQUIRED;
            case 401:
                return Response::HTTP_UNAUTHORIZED;
            case 400:
                return Response::HTTP_BAD_REQUEST;
            case 308:
                return Response::HTTP_PERMANENT_REDIRECT;
            case 307:
                return Response::HTTP_TEMPORARY_REDIRECT;
            case 306:
                return Response::HTTP_SWITCH_PROXY;
            case 305:
                return Response::HTTP_USE_PROXY;
            case 304:
                return Response::HTTP_NOT_MODIFIED;
            case 303:
                return Response::HTTP_SEE_OTHER;
            case 302:
                return Response::HTTP_FOUND;
            case 301:
                return Response::HTTP_MOVED_PERMANENTLY;
            case 300:
                return Response::HTTP_MULTIPLE_CHOICES;
            case 226:
                return Response::HTTP_IM_USED;
            case 208:
                return Response::HTTP_ALREADY_REPORTED;
            case 206:
                return Response::HTTP_PARTIAL_CONTENT;
            case 205:
                return Response::HTTP_RESET_CONTENT;
            case 204:
                return Response::HTTP_NO_CONTENT;
            case 202:
                return Response::HTTP_ACCEPTED;
            case 201:
                return Response::HTTP_CREATED;
            case 103:
                return Response::HTTP_EARLY_HINTS;
            case 102:
                return Response::HTTP_PROCESSING;
            case 101:
                return Response::HTTP_SWITCHING_PROTOCOLS;
            case 100:
                return Response::HTTP_CONTINUE;           
            default:
                return Response::HTTP_OK; // 200
        }

    }
    
    public function json($data, $status = null) {
             
        $encoders = new JsonEncoder();
        $normalizers = new GetSetMethodNormalizer();
        $serializer = new Serializer ( array (
        new DateTimeNormalizer(), $normalizers), array($encoders));        
        $json=$serializer->serialize($data, 'json');
        $response = new Response;
        $response->setContent(utf8_encode($json));
        $response->setStatusCode( $this->getHttpStatusCode($status) );
        $response->headers->set('Content-Type', 'application/json');
        return $response;        
    }

    public function jsonWithInfoPage($data) {
             
        return $this->json([
                    'ok' => true,
                    'message' => 'Success',
                    'data' => $data['data'],
                    'total' => $data['total'],
                    'totalPage' => $data['totalPage'],
                    'currentPage' => $data['currentPage']
                ], 200);        
    }

    public function returnRequest($data = null, $message = null, $status = null){

        $return = [];
        if($message){ $return['message'] = $message; }
        if($data){ $return['data'] = $data; }
        return $this->json($return, $status);
    }

    // Manage Entities

    public function setParametersToEntity($entity, $parameters, $ignore = [] ){

        $entityName = $this->manager->getMetadataFactory()->getMetadataFor(get_class($entity))->getName();
        $fieldMappings = $this->manager->getClassMetadata( $entityName )->fieldMappings;
        $associationMappings = $this->manager->getClassMetadata( $entityName )->associationMappings;
        //dump($fieldMappings);die;
        foreach ($parameters as $key => $value) {

            if (!in_array($key, $ignore) ){
                if( isset($fieldMappings[$key]) ){
                    // covert property value type integer or date if it is nescesary.
                    if( $fieldMappings[$key]['type'] == 'integer' ){
                        $value = (int)$value;
                    }else if( $fieldMappings[$key]['type'] == 'date' ){
                        $value = \DateTime::createFromFormat('Y-m-d', date('Y-m-d', strtotime(str_replace('-','/', $value ))) );
                    }else if( $fieldMappings[$key]['type'] == 'time' ){
                        $value = explode(':', $value);
                        $d=mktime($value[0], $value[1], $value[2]); 
                        $value = date("H:i:s", $d);
                    }else if( $fieldMappings[$key]['type'] == 'string' ){
                            $value = $value;
                        // lowercase
                       // $value = strtolower($value);
                    }else if( $fieldMappings[$key]['type'] == 'smallint' ){
                        $value = $value;
                    }else if( $fieldMappings[$key]['type'] == 'decimal' ){
                        $value = $value == '' ? 0 : number_format($value, 2); 
                    }else if( $fieldMappings[$key]['type'] == 'datetime' ){
                        $value = \DateTime::createFromFormat('Y-m-d', date('Y-m-d', strtotime(str_replace('-','/', $value ))) );
                    }else{
                        dump($fieldMappings[$key]['type']);die;
                    }

                    // Build Method Set.
                    $method = $this->buildMethodSet($key);
                    $entity->$method($value);
                } else if ( isset($associationMappings[$key]) ){ // check if it is an association between entities
                    $entityAssociation = $this->manager->getRepository($associationMappings[$key]['targetEntity'])->findOneById($value);
                    if($associationMappings[$key] ["type"]!=4&&$associationMappings[$key] ["type"]!=8){
                        $method = $this->buildMethodSet($key);
                        $entity->$method($entityAssociation);

                    }
                }
            }
        }
        return $entity;
    }

    public function buildMethodSet($property){
        $arr = explode("_",$property); // e.g. in = language_source
        foreach ($arr as $x => &$val) {
          $val = ucfirst($val);
        }
        $method = 'set' . join("",$arr); // e.g. out = setLanguageSource
        return $method;
    }

    public function saveEntity($entity){

        $this->manager->persist($entity);
        $this->manager->flush();
        return $entity;
    }

    public function removeEntity($entity){

        $this->manager->remove($entity);
        $this->manager->flush();
    }

    public function validateEntity($entity){
        $errors = $this->validator->validate($entity);
        //dump($errors);die;
        if ( count($errors) > 0) {
            $arrayError = [];
            foreach ($errors as $x => $err) {
                //$arrayError[$x] = preg_replace('/\s+/', ' ', $err->getMessage() . ' ' . $err->getInvalidValue() . ' ' . $err->getPropertyPath() );
                $arrayError[$x] = ['message' => $err->getMessage(), 'value' => $err->getInvalidValue(), 'property' => $err->getPropertyPath()];
            }
            return $arrayError;
        }else{
            return null;
        }
    }

    // Mannage connectiosn SQL

    public function calculateInfoPage($query, $queryString = [], $parameters = []){

        $total = (int)$this->aplicateConn($query, $parameters)[0]['total'];
        $limit = (array_key_exists('limit', $queryString) ? (int)$queryString['limit'] < 1 ? 10 : (int)$queryString['limit'] : 10);
        $page = (array_key_exists('page', $queryString) ? (int)$queryString['page'] < 1 ? 1 : (int)$queryString['page'] : 1);
        $totalPage = (int)ceil($total / $limit);
        $offset = ( ($limit * $page ) - $limit);
        return ['total' => $total, 'limit' => $limit, 'page' => $page, 'totalPage' => $totalPage, 'offset' => $offset];
    }

    public function aplicateConn($query, $parameters = []){ 

        $conn = $this->manager->getConnection();
        $stmt = $conn->prepare($query);
        $stmt->execute($parameters);
        //dump( $stmt );die;
        return $stmt->fetchAll();
    }

    public function buildFieldsString($queryString, $fieldAvailable){ 

        // fields to select in sql
        $fieldString = '';
        // count index
        $index = 0;
        // filter by some properties
        if( array_key_exists('fields', $queryString) ){ // must come in path eg. fields=id,username,lastname
            // Get fields to select
            $fields = explode(",", $queryString['fields']);
            // Get id by default
            array_unshift($fields, "id");
            // Loop fields to select
            foreach ($fields as $key => $field) {
                if( isset($fieldAvailable[$field]) ){             
                    $fieldString .= $index > 0 ? ', ' : '';
                    $fieldString .= $fieldAvailable[$field];
                    $index ++;
                }
            }
        }else{ // get all properties availables
            // Loop fields to select
            foreach ($fieldAvailable as $key => $field) {
                $fieldString .= $index > 0 ? ', ' : '';
                $fieldString .= $fieldAvailable[$key];
                $index ++;
            }
        }
        return $fieldString;
    }

}