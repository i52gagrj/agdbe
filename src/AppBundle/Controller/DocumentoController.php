<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use ModelBundle\Entity\Usuario;
use ModelBundle\Entity\Documento;
use AppBundle\Services\Helpers;
use AppBundle\Services\JwtAuth;

class DocumentoController extends Controller {

	/*public function pruebaAction(Request $request) {
        $helpers = $this->get(Helpers::class);
		$jwt_auth = $this->get(JwtAuth::class);

		$uploadedFichero = $request->files->get('file');

		$token = $request->get('authorization');

		$token = $jwt_auth->checkToken($token);

		$usuario = $jwt_auth->returnUser($request->get('usuario', null));

		if($uploadedFichero)
		{			
			// /**
			//   * @var UploadedFile $fichero;
			//  */ /*				
			$fichero = $uploadedFichero;
			$nombrefichero=md5(uniqid()).'.'.$fichero->guessExtension();
			$fichero->move($this->getParameter('directorio_documentos'),$nombrefichero);

			if($usuario){
				$data = array(
					'status' => 'success',
					'code' => 200,
					'msg' => 'File moved with user',
					'token' => $token,
					'usuario' => $usuario
				); 
			}
			else
			{
				$data = array(
					'status' => 'success',
					'code' => 200,
					'msg' => 'File moved without user',
					'token' => $token,
					'usuario' => $usuario
				); 
			}
		}
		else
		{
			$data = array(
				'status' => 'error',
				'code' => 400,
				'msg' => 'File not found'
			); 
		}

		return new JsonResponse($data);
		//return $helpers->json($data);

	}*/

	public function newAction(Request $request) {
        $helpers = $this->get(Helpers::class);
		$jwt_auth = $this->get(JwtAuth::class);

		/* NOTA
		Hay que añadir una condición para que solo puedan añadir documentos los usuarios clientes, y no lo puedan hacer los administradores
		En el caso de modelos, será simetrico: solo podrán añadirlos los administradores, y no los clientes.
		*/
		
		// Requerir autorización


        $token = $request->get('authorization');
        $authCheck = $jwt_auth->checkToken($token);

        $data = array(
            'status' => 'error',
            'code' => 400,
            'msg' => 'Authorization not valid !!'
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
				$fichero->move($this->getParameter('directorio_documentos'),$nombrefichero);
			
				$documento = new Documento();
				$documento->setRuta($nombrefichero);
				$documento->setDescripcion($params->descripcion);
				$documento->setTipo($tipo);				
				$documento->setFechahora(new \Datetime("now"));
				$documento->setUsuario($identity->getId());       

				$em = $this->getDoctrine()->getManager();
				$em->persist($documento);
				$em->flush();   
				
				$datosdocumento = array(
					'Id' => $documento->getId(),
					'Descripcion' => $documento->getDescripcion(),					
					'Tipo' => $documento->getTipo()
				);
				
				$data = array(
					'status' => 'Success',
					'code' => 200, 					
					'token' => $authCheck,
					'msg' => 'New Document created!!',
					'documento' => $documento
				);    
			}
			else
			{
				$data = array(
					'status' => 'error',
					'code' => 400,
					'token' => $authCheck,
					'msg' => 'File not send'
				); 				
			}
		       		
        }
        
		return $helpers->json($data);
			
	}	

	 
	public function listallAction(Request $request) {
		// Devuelve el listado de todos los documentos de un cliente
		// La idea es que devuelva la descripción y los datos, no la ruta!!

		// Si se pasa el usuario como parametro, se devolverán los documentos del usuario (descripción y datos, no ruta)
		// Esto serviria para que los administradores pasen el id de un usario y recuperen sus documentos
		// Si no se pasa, se recupera el usario del token
		// Esta manera servirá para que los usuarios recuperen el listado de sus documentos
		
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);

        $token = $request->get('authorization', null);
		$authCheck = $jwt_auth->checkToken($token);
		$id = $request->get('id', null);

		$data = array(
			'status' => 'error',
			'code' => 400,
			'msg' => 'Authorization not valid !!'
		); 
		
        if($authCheck){		
			//$decode = $jwt_auth->decodeToken($token);
			//$identity = $jwt_auth->returnUser($decode->sub);				

			/*
			Buscar los documentos creados por el usuario indicado, ordenados por fecha
			*/
			$em = $this->getDoctrine()->getManager();			

			$dql = "SELECT d FROM ModelBundle:Documento d "
                ."WHERE d.usuario = $id"
				."ORDER BY d.fechahora ASC";

			$query = $em->createQuery($dql);
	
			$documentos = $query->getResult();

			//FALTARIA PAGINARLOS

			if($documentos){	
				$data = array(
					'status' => 'success',
					'code' => 200,
					'token' => $authCheck,                    
					'documentos' => $documentos
				);    
			}else{
				$data = array(
					'status' => 'success',
					'code' => 200,
					'token' => $authCheck,                    
					'documentos' => "No hay documentos"
				);    				
			}			

		}

		return $helpers->json($data);	
	}
	

	 
	public function returnoneAction(Request $request) {		
		// Devuelve la ruta de un documento por la id
		/*
		Recuperamos la autorización
		De ser correcta, se busca el documento por la id pasada
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