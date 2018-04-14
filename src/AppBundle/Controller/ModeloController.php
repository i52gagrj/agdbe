<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints as Assert;
use ModelBundle\Entity\Usuario;
use AppBundle\Services\Helpers;
use AppBundle\Services\JwtAuth;

class ModeloController extends Controller {

	public function newAction(Request $request) {
        $helpers = $this->get(Helpers::class);
		$jwt_auth = $this->get(JwtAuth::class);

		/* NOTA
		Hay que añadir una condición para que solo puedan añadir modelos los usuarios clientes, y no lo puedan hacer los administradores
		En el caso de modelos, será simetrico: solo podrán añadirlos los administradores, y no los clientes.
		*/
		
		// Requerir autorización


        $token = $request->get('authorization');
        $authCheck = $jwt_auth->checkToken($token);

        $data = array(
            'status' => 'error',
            'code' => 400,
            'msg' => 'Model not created !!'
        );         

        if($authCheck)
        {        			
			// Recuperar la identidad del usuario
			$decoded = $jwt_auth->decodeToken($token);
			$identity = $jwt_auth->returnUser($decoded->sub);

			// Requerir los datos json enviados
	        $json = $request->get('json', null);
			$params = json_decode($json);

			// Requerir fichero
			$uploadedFichero = $request->files->get('file');			
			
			if($uploadedFichero)
			{														
				/**
				  * @var UploadedFile $fichero;
				  */ 				
				$fichero = $uploadedFichero;
				$tipo = $fichero->guessExtension();
				$nombrefichero=md5(uniqid()).'.'.$tipo;
				$fichero->move($this->getParameter('directorio_modelos'),$nombrefichero);				
				$modelo = new modelo();
				$modelo->setRuta($nombrefichero);
				$modelo->setDescripcion($params->descripcion);
				$modelo->setCodigo($params->codigo);
				$modelo->setTrimestre($params->trimestre);
				$modelo->setEjercicio($params->ejercicio);
				$modelo->setTipo($tipo);				
				//$modelo->setFechahora(new \Datetime("now"));
				$modelo->setUsuario($identity);       

				$em = $this->getDoctrine()->getManager();
				$em->persist($modelo);
				$em->flush();   
				
				$datosmodelo = array(
					'Id' => $modelo->getId(),
					'Descripcion' => $modelo->getDescripcion(),					
					'Tipo' => $modelo->getTipo()
				);
				
				$data = array(
					'status' => 'Success',
					'code' => 200,
					'msg' => 'New Document created!!', 					
					'token' => $authCheck,
					'modelo' => $datosmodelo
				);    
			}
			else
			{
				$data = array(
					'status' => 'error',
					'code' => 400,
					'msg' => 'File not send', 
					'descripcion' => $params->descripcion,
					'token' => $authCheck
				); 				
			}
		       		
        }

        else 
        {    		
	        $data = array(
	            'status' => 'error',
	            'code' => 400,
	            'msg' => 'Authorization not valid !!',
				'authcheck' => $authCheck,
				'token' => $token
	        ); 
        }
        
		return $helpers->json($data);
			
	}	
	
	public function listallAction(Request $request) {
		// Devuelve el listado de todos los modelos de un cliente
		// La idea es que devuelva la descripción y los datos, no la ruta!!

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
        	$id=$request->get('idmodelo', null);
        	$em = $this->getDoctrine()->getManager();
        	$modelo = $em->getRepository('ModelBundle:modelo')->find($id);

        	if($modelo) {
			    $data = array(
			        'status' => 'Success',
			        'code' => 200,
			        'msg' => 'Document recovered !!', 
			        'authcheck' => $authCheck,
			        'modelo' => $modelo
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

		echo "Hola mundo desde el controlador de listar modelos";
		die();		
	}
	

	 
	public function returnoneAction(Request $request) {		
		// Devuelve la ruta de un modelo por la id
		/*
		Recuperamos la autorización
		De ser correcta, se busca el modelo por la id pasada
		Si está, se devuelve este
		Si no está, se devuelve un mensaje de no encontrado
		De ser incorrecta la autorización, se devuelve un mensaje de error en la misma
	 	*/		

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
        	$id=$request->get('idmodelo', null);
        	$em = $this->getDoctrine()->getManager();
        	$modelo = $em->getRepository('ModelBundle:modelo')->find($id);

        	if($modelo) {
			    $data = array(
			        'status' => 'Success',
			        'code' => 200,
			        'msg' => 'Document recovered !!', 
			        'authcheck' => $authCheck,
			        'modelo' => $modelo
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


		echo "Hola mundo desde el controlador de devolver un modelo";
		die();		
	}	

}	