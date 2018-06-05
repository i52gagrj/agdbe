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
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Filesystem\Filesystem;
use ModelBundle\Entity\Usuario;
use ModelBundle\Entity\Documento;
use AppBundle\Services\Helpers;
use AppBundle\Services\JwtAuth;

class DocumentoController extends Controller {

	public function newAction(Request $request) {
        $helpers = $this->get(Helpers::class);
		$jwt_auth = $this->get(JwtAuth::class);

		// NOTA
		//Hay que añadir una condición para que solo puedan añadir documentos los usuarios clientes, y no lo puedan hacer los administradores
		//En el caso de modelos, será simetrico: solo podrán añadirlos los administradores, y no los clientes.		
		
		// Requerir autorización


        $token = $request->get('authorization');
        $authCheck = $jwt_auth->checkToken($token);

        $data = array(
            'status' => 'error',
            'code' => 405,
			'msg' => 'Authorization not valid !!',
			'token' => $token
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
				if($fichero!=""){
					$tipo = $fichero->guessExtension();
					$nombrefichero=md5(uniqid()).'.'.$tipo;
					$fichero->move($this->getParameter('directorio_documentos'),$nombrefichero);
				
					$documento = new Documento();
					$documento->setRuta($nombrefichero);
					$documento->setDescripcion($params->descripcion);
					$documento->setTipo($tipo);				
					$documento->setFechahora(new \Datetime("now"));
					$documento->setUsuario($identity->getId());    
					$documento->setVisto(false);  

					$em = $this->getDoctrine()->getManager();
					$em->persist($documento);
					$em->flush();   
					
					$datosdocumento = array(
						'Id' => $documento->getId(),
						'Descripcion' => $documento->getDescripcion(),					
						'Tipo' => $documento->getTipo()
					);
					
					$data = array(
						'status' => 'success',
						'code' => 200, 					
						'token' => $authCheck,
						'msg' => 'New Document created!!',
						'documento' => $documento
					);   
				}else{
					$data = array(
						'status' => 'error',
						'code' => 400,
						'token' => $authCheck,
						'msg' => 'File too big',
						'token' => $token
					);  					
				}
			}
			else
			{
				$data = array(
					'status' => 'error',
					'code' => 400,
					'token' => $authCheck,
					'msg' => 'File not send',
					'token' => $token
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
		

		$data = array(
			'status' => 'error',
			'code' => 405,
			'msg' => 'Authorization not valid !!'
		); 
		
        if($authCheck){		
			$decode = $jwt_auth->decodeToken($token);
			//$identity = $jwt_auth->returnUser($decode->sub);						
			$id = $request->get('id', null);

			if($id){
				$identity = $jwt_auth->returnUser($id);
				if($identity && $decode->isadmin && $identity->getAdmin() == $decode->sub){

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
				

			if(($id && $decode->isadmin) || ($userid && !$decode->isadmin)){
				//Buscar los documentos creados por el usuario indicado, ordenados por fecha
				$em = $this->getDoctrine()->getManager();			

				$dql = "SELECT d.id, d.descripcion, d.fechahora, d.tipo, d.visto "
				."FROM ModelBundle:Documento d "
				."WHERE d.usuario = {$userid} "
				."ORDER BY d.fechahora DESC";

				$query = $em->createQuery($dql);

				//Paginarlos
				/*$page = $request->query->getInt('page', 1);
				$paginator = $this->get('knp_paginator');
				$items_per_page = 10;
				$pagination = $paginator->paginate($query, $page, $items_per_page);
				$total_items_count = $pagination->getTotalItemCount();*/
		
				$documentos = $query->getResult();						

				if($documentos){	
					$data = array(
						'status' => 'success',
						'code' => 200,
						'token' => $authCheck,                    
						/*'total_items_count' => $total_items_count,
						'page_actual' => $page,
						'items_per_page' => $items_per_page,
						'total_pages' => ceil($total_items_count / $items_per_page),*/
						'data' => $documentos
					);    
				}else{
					$data = array(
						'status' => 'success',
						'code' => 200,
						'token' => $authCheck,    
						'data' => null,                
						'message' => "No hay documentos"
					);    				
				}	
			}else {
				$data = array(
					'status' => 'error',
					'code' => 400,
					'token' => $authCheck,
					'data' => null,
					'msg' => 'user admin, id not provided !!'
				); 				
			}		

		}

		return $helpers->json($data);	
	}		 


	public function returnoneAction(Request $request) {		
		// Devuelve el documento con la id indicada, comprobando previamente si pertenece al usuario 
		// que ha realizado la petición, mediante la id del token		

		// Si se pasa el usuario como parametro, se devolverán los documentos del usuario (descripción y datos, no ruta)
		// Esto serviria para que los administradores pasen el id de un usario y recuperen sus documentos
		// Si no se pasa, se recupera el usario del token
		// Esta manera servirá para que los usuarios recuperen el listado de sus documentos

		// IMPLEMENTAR LA DESCARGA POR PARTE DE UN ADMIN
		
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);

        $token = $request->get('authorization', null);
		$authCheck = $jwt_auth->checkToken($token);
		$file = null;
		
		$data = array(
			'status' => 'error',
			'code' => 405,
			'msg' => 'Authorization not valid !!'
		); 
		
        if($authCheck){		
			$decode = $jwt_auth->decodeToken($token);
	        $id = $request->get('id', null);

			if($id){
				// Buscar el documento indicado				
				$em = $this->getDoctrine()->getManager();			
					
				$documento = $em->getRepository('ModelBundle:Documento')->find($id);

				if($documento){
					if($decode->sub != $documento->getUsuario() && !$decode->isadmin){
						$data = array(
							'status' => 'error',
							'code' => 400,
							'msg' => 'User not admin !!'
						);
					}else{
						$file = new File($this->getParameter('directorio_documentos').'/'.$documento->getRuta());						
						$data = array(
							'status' => 'success',
							'code' => 200,
							'id' => $id,
							'token' => $authCheck,
							'file' => $file
						);   	
						//$data = $helpers->json($data2);						
					}	
				}else{
					$data = array(
						'status' => 'success',
						'code' => 200,
						'id' => $id,
						'token' => $authCheck,                    
						'message' => "El documento no existe"
					);    				
				}	
			}else{
				$data = array(
					'status' => 'error',
					'code' => 400,
					'msg' => 'id not send !!'
				);				
			}		

		}
				
		if($file){ 
			//$mandar = new Response($data);
			//$mandar->headers->set('Content-Type', 'multipart/form-data');
			//return $mandar;
			return $this->file($file);
		}
		else{ 
			return $helpers->json($data);
		}
	}


	public function deleteAction(Request $request) {		
		$helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);

        $token = $request->get('authorization', null);
		$authCheck = $jwt_auth->checkToken($token);
		
		$data = array(
			'status' => 'error',
			'code' => 405,
			'msg' => 'Authorization not valid !!'
		); 
		
        if($authCheck){		
			$decode = $jwt_auth->decodeToken($token);					
	        $id = $request->get('id', null);

			if($id){
				//Buscar el documento indicado
				$em = $this->getDoctrine()->getManager();			
					
				$documento = $em->getRepository('ModelBundle:Documento')->find($id);

				if($documento && is_object($documento)){
					if($decode->sub != $documento->getUsuario()){						
						$data = array(
							'status' => 'error',
							'code' => 400,
							'msg' => 'User not owner !!'
						);
					}else{
						//Borrar documento						
						$filename = $this->getParameter('directorio_documentos').'/'.$documento->getRuta();

						$filesystem = new Filesystem();
						$filesystem->remove($filename);

						$em->remove($documento);
						$em->flush();
						$data = array(
							'status' => 'success',
							'code' => 200,
							'id' => $id,
							'token' => $authCheck,
							'message' => "Documento borrado"                    							
						);   						
					}	
				}else{
					$data = array(
						'status' => 'error',
						'code' => 400,
						'id' => $id,
						'token' => $authCheck,                    
						'message' => "El documento no existe"
					);    				
				}	
			}else{
				$data = array(
					'status' => 'error',
					'code' => 400,
					'msg' => 'id not send !!'
				);				
			}		

		}

		return $helpers->json($data);		
	}	

	
	public function listnewAction(Request $request) {
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
			'code' => 405,
			'msg' => 'Authorization not valid !!'
		); 
		
        if($authCheck){		
			$decode = $jwt_auth->decodeToken($token);
			$identity = $jwt_auth->returnUser($decode->sub);									
			//$id = $request->get('id', null);

			if($decode->isadmin){				
				//Buscar los documentos creados por el usuario indicado, ordenados por fecha
				$em = $this->getDoctrine()->getManager();			

				$id = $decode->sub;

				/*$dql = "SELECT d.id, d.descripcion, d.tipo, d.usuario, d.fechahora, d.visto " 
					."FROM ModelBundle:Documento d " 
					."WHERE d.usuario in "
					."(SELECT u.id FROM ModelBundle:Usuario u WHERE u.admin = $id) "
					."AND d.visto = false ORDER BY d.id DESC";*/

				$dql = "SELECT d.id, d.descripcion, d.fechahora, d.tipo, d.visto, u2.nombre" 
					." FROM ModelBundle:Documento d, ModelBundle:Usuario u2" 
					." WHERE d.usuario = u2.id"
					." AND d.usuario in"
					." (SELECT u.id FROM ModelBundle:Usuario u WHERE u.admin = $id)"
					." AND d.visto = false"
					." ORDER BY d.fechahora DESC";

				$query = $em->createQuery($dql);				

				//Paginarlos				
				/*$page = $request->query->getInt('page', 1);
				$paginator = $this->get('knp_paginator');
				$items_per_page = 10;
				$pagination = $paginator->paginate($query, $page, $items_per_page, array('distinct' => false));
				$total_items_count = $pagination->getTotalItemCount();*/

				if($query->getResult()){	
					$data = array(
						'status' => 'success',
						'code' => 200,
						'token' => $authCheck,                    
						'data' => $query->getResult()/*
						'total_items_count' => $total_items_count,
						'page_actual' => $page,
						'items_per_page' => $items_per_page,
						'total_pages' => ceil($total_items_count / $items_per_page),
						'data' => $pagination*/
						
					);    
				}else{
					$data = array(
						'status' => 'success',
						'code' => 200,
						'token' => $authCheck,    
						'data' => null,                
						'message' => "No hay documentos"
					);    				
				}				
			}else{
				$data = array(
					'status' => 'error',
					'code' => 400,
					'msg' => 'User not admin !!'
				);				
			}

		}

		return $helpers->json($data);	
	}	

	public function listpruebaAction(Request $request) {
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
			'code' => 405,
			'msg' => 'Authorization not valid !!'
		); 
		
        if($authCheck){		
			$decode = $jwt_auth->decodeToken($token);
			$identity = $jwt_auth->returnUser($decode->sub);												

			if($decode->isadmin){				
				//Buscar los documentos creados por el usuario indicado, ordenados por fecha
				$em = $this->getDoctrine()->getManager();			

				$id = $decode->sub;

				$dql = "SELECT d.id, d.descripcion, d.fechahora, d.tipo, d.visto, u2.nombre as " 
					." FROM ModelBundle:Documento d, ModelBundle:Usuario u2" 
					." WHERE d.usuario = u2.id"					
					." AND d.usuario in"					
					." (SELECT u.id FROM ModelBundle:Usuario u WHERE u.admin = $id)"					
					." AND d.visto = false"
					." ORDER BY d.fechahora DESC";

				$query = $em->createQuery($dql);																						

				if($query->getResult()){	
					$data = array(
						'status' => 'success',
						'code' => 200,
						'token' => $authCheck,                    
						'data' => $query->getResult()
					);    
				}else{
					$data = array(
						'status' => 'success',
						'code' => 200,
						'token' => $authCheck,    
						'data' => null,                
						'message' => "No hay documentos"
					);    				
				}				
			}else{
				$data = array(
					'status' => 'error',
					'code' => 400,
					'msg' => 'User not admin !!'
				);				
			}

		}

		return $helpers->json($data);	
	}		

	public function cambiarestadoAction(Request $request) {		
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);

        $token = $request->get('authorization', null);
		$authCheck = $jwt_auth->checkToken($token);		

		$data = array(
			'status' => 'error',
			'code' => 405,
			'msg' => 'Authorization not valid !!'
		);
		
        if($authCheck){		
			$decode = $jwt_auth->decodeToken($authCheck);	
			$id = $request->get('id', null);		
			$nuevoestado = $request->get('estado', null);		

			if($id && $nuevoestado != null && ($nuevoestado == true || $nuevoestado == false) ){
				$em = $this->getDoctrine()->getManager();
				$documento = $em->getRepository('ModelBundle:Documento')->find($id);

				if($documento){
					if($documento->getUsuario() == $decode->sub || $decode->isadmin){
						$documento->setVisto($nuevoestado);
						$em->persist($documento);
						$em->flush();
						$data = array(
							'status' => 'success',
							'code' => 200,
							'token' => $authCheck,  
							'nuevoestado' => $nuevoestado,                  
							'data' => $documento,
							'msg' => 'Documento actualizado'
						);
					}else{
						$data = array(
							'status' => 'error',
							'code' => 400,
							'token' => $authCheck,						
							'msg' => 'User not authorized'
						);
					}
				}else{
					$data = array(
						'status' => 'error',
						'code' => 400,
						'token' => $authCheck,						
						'msg' => 'No existe el documento'
					);
				}				
			}else{				
				$data = array(
					'status' => 'error',
					'code' => 400,
					'token' => $authCheck,
					'data' => null,
					'msg' => 'id or new status not provided !!'
				); 				
			}				
		}

		return $helpers->json($data);	
	}	
}