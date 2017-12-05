<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints as Assert;
use ModelBundle\Entity\Usuario;
use ModelBundle\Entity\Documento;
use AppBundle\Services\Helpers;
use AppBundle\Services\JwtAuth;

class DocumentoController extends Controller {

	public function newAction(Request $request) {
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);

        $token = $request->get('authorization', null);
        $authCheck = $jwt_auth->checkToken($token);

        $data = array(
            'status' => 'error',
            'code' => 400,
            'msg' => 'Document not created !!'
        );         

        if($authCheck)
        {        	
	        $json = $request->get('json', null);
	        $params = json_decode($json);

	        if($json != null)
	        {        	
	        	$fechahora = new \Datetime("now");
	        	$descripcion = (isset($params->descripcion)) ? $params->descripcion : null;
	        	$tipo = (isset($params->tipo)) ? $params->tipo : null;
	        	$contenido= (isset($params->contenido)) ? $params->contenido : null;
	        }

        	$documento = new Documento();
        	$documento->setDescripcion($descripcion);
        	$documento->setTipo($tipo);
        	$documento->setFechahora($fechahora);
        	$documento->setContenido($contenido);

        	$em = $this->getDoctrine()->getManager();
        	$em->persist($documento);
        	$em->flush();        			

		    $data = array(
		        'status' => 'Success',
		        'code' => 200,
		        'msg' => 'New Document created !!', 
		        'authcheck' => $authCheck,
		        'documento' => $documento
		    );    
		       		
        }

        else 
        {    		
	        $data = array(
	            'status' => 'error',
	            'code' => 400,
	            'msg' => 'Authorization not valid !!', 
		        'authcheck' => $authCheck
	        ); 
        }
        
		return $helpers->json($data);	
			
	}	

	 
	public function listallAction(Request $request) {
		// Devuelve el listado de todos los documentos de un cliente

		echo "Hola mundo desde el controlador de listar Documentos";
		die();		
	}
	

	 
	public function returnoneAction(Request $request) {		
		// Devuelve el contenido de un documento por la id

		/*

    	$helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);

        $token = $request->get('authorization', null);

        $authCheck = $jwt_auth->checkToken($token);

        $data = array(
            'status' => 'error',
            'code' => 400,
            'msg' => 'Document not created !!'
        );         

        if($authCheck)
        {   
        	$id=$request->get('iddocumento', null);
        	$em = $this->getDoctrine()->getManager();
        	$documento = $em->getRepository('ModelBundle:Documento')->find($id);

        	if($documento) {
			    $data = array(
			        'status' => 'Success',
			        'code' => 200,
			        'msg' => 'Document recovered !!', 
			        'authcheck' => $authCheck,
			        'documento' => $documento
			    );            		
        	}

        	else {
			    $data = array(
			        'status' => 'error',
			        'code' => 400,
			        'msg' => 'Document not exist !!', 
			        'authcheck' => $authCheck
			    );            		        		
        	}    			          	
        }     			
		
		else
		{
	        $data = array(
    	        'status' => 'error',
        	    'code' => 400,
            	'msg' => 'Authorization not valid'
        	);         
		}	

		return $helpers->json($data);
		
		*/		
		echo "Hola mundo desde el controlador de devolver un Documento";
		die();		
	}
	

}