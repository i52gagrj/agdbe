<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints as Assert;
use ModelBundle\Entity\Usuario;
use ModelBundle\Entity\Modelo;
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
			// Requerir los datos json enviados
	        $json = $request->get('json', null);
			$params = json_decode($json);

			// Requerir fichero
			$uploadedFichero = $request->files->get('file');

			// Requerir usuario
			$usuario = $jwt_auth->returnUser($params->usuario);			
			
			if($uploadedFichero && $usuario)
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
				$modelo->setFechahora(new \Datetime("now"));
				$modelo->setUsuario($usuario->getId());       

				$em = $this->getDoctrine()->getManager();
				$em->persist($modelo);
				$em->flush();   
				
				$data = array(
					'status' => 'Success',
					'code' => 200,
					'msg' => 'New Document created!!', 					
					'token' => $authCheck,
					'modelo' => $modelo
				);    
			}
			else
			{
				$data = array(
					'status' => 'error',
					'code' => 400,
					'msg' => 'File not send or user not valid', 
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
	            'msg' => 'Authorization not valid !!'
	        ); 
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
		

		$data = array(
			'status' => 'error',
			'code' => 400,
			'msg' => 'Authorization not valid !!'
		); 
		
        if($authCheck){		
			$decode = $jwt_auth->decodeToken($token);
			//$identity = $jwt_auth->returnUser($decode->sub);						
			$id = $request->get('id', null);

			if($id){
				if($decode->rol=="admin"){
					$userid = $id;
				}else{
					$userid = null;
					$data = array(
						'status' => 'error',
						'code' => 400,
						'msg' => 'User not admin !!'
					); 
				}
			}else{
				$userid = $decode->sub;
			}
				

			if($userid){
				//Buscar los documentos pertenecientes al usuario indicado, ordenados por fecha
				$em = $this->getDoctrine()->getManager();			

				$dql = "SELECT m FROM ModelBundle:Modelo m WHERE m.usuario = {$userid} ORDER BY m.fechahora ASC";

				$query = $em->createQuery($dql);

				//Paginarlos
				$page = $request->query->getInt('page', 1);
				$paginator = $this->get('knp_paginator');
				$items_per_page = 10;
				$pagination = $paginator->paginate($query, $page, $items_per_page);
				$total_items_count = $pagination->getTotalItemCount();			
		
				$documentos = $query->getResult();				

				if($documentos){	
					$data = array(
						'status' => 'success',
						'code' => 200,
						'token' => $authCheck,                    
						'total_items_count' => $total_items_count,
						'page_actual' => $page,
						'items_per_page' => $items_per_page,
						'total_pages' => ceil($total_items_count / $items_per_page),
						'data' => $pagination
					);    
				}else{
					$data = array(
						'status' => 'success',
						'code' => 200,
						'id' => $userid,
						'token' => $authCheck,                    
						'message' => "No hay modelos"
					);    				
				}	
			}		

		}

		return $helpers->json($data);	
	}	
	/*public function listallAction(Request $request) {
		// Devuelve el listado de todos los modelos de un cliente
		// La idea es que devuelva la descripción y los datos, no la ruta!!

		// Si se pasa el usuario como parametro, se devolverán los modelos del usuario (descripción y datos, no ruta)
		// Esto serviria para que los administradores pasen el id de un usuario y recuperen sus modelos
		// Si no se pasa, se recupera el usario del token
		// Esta manera servirá para que los usuarios recuperen el listado de sus modelos
		
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);

        $token = $request->get('authorization', null);
		$authCheck = $jwt_auth->checkToken($token);
		$decoded = $jwt_auth->decodeToken($token);
		$identity = $jwt_auth->returnUser($decoded->sub);
		$id = $request->get('id', null);

		if($id && $decoded->rol != 'admin'){
			$authCheck = null;
		}

		if(!$id){
			$id = ($jwt_auth->decodeToken($token))->sub;
		}

		$data = array(
			'status' => 'error',
			'code' => 400,
			'msg' => 'Authorization not valid !!'
		); 
		
        if($authCheck){		
			//$decode = $jwt_auth->decodeToken($token);
			//$identity = $jwt_auth->returnUser($decode->sub);				
			
			//Buscar los modelos asignado al usuario indicado, ordenados por fecha		
			$em = $this->getDoctrine()->getManager();			

			$dql = "SELECT m FROM ModelBundle:Modelo m "
                ."WHERE m.usuario = $id"
				."ORDER BY m.fechahora ASC";

			$query = $em->createQuery($dql);
	
			$modelos = $query->getResult();

			//FALTARIA PAGINARLOS

			if($modelos){	
				$data = array(
					'status' => 'success',
					'code' => 200,
					'token' => $authCheck,                    
					'modelos' => $modelos
				);    
			}else{
				$data = array(
					'status' => 'success',
					'code' => 200,
					'token' => $authCheck,                    
					'modelos' => "No hay modelos"
				);    				
			}			

		}

		return $helpers->json($data);	
	}*/
	

	 
	public function returnoneAction(Request $request) {		
		echo "Hola mundo desde el controlador de devolver un modelo";
		die();		
	}	

}	