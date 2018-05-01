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
					'status' => 'success',
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
					'msg' => 'File not send',
					'token' => $token
				); 				
			}
		       		
		}
		else{

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
				if($decode->rol=="admin"){
					$userid = $id;
					//FALTA CONFIRMAR QUE EL ADMINISTRADOR ES EL DEL USUARIO
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
				//Buscar los documentos creados por el usuario indicado, ordenados por fecha
				$em = $this->getDoctrine()->getManager();			

				$dql = "SELECT d FROM ModelBundle:Documento d WHERE d.usuario = {$userid} ORDER BY d.fechahora DESC";

				$query = $em->createQuery($dql);

				//Paginarlos
				$page = $request->query->getInt('page', 1);
				$paginator = $this->get('knp_paginator');
				$items_per_page = 10;
				$pagination = $paginator->paginate($query, $page, $items_per_page);
				$total_items_count = $pagination->getTotalItemCount();			
		
				$documentos = $query->getResult();		
				
				$then = new \Datetime("+15 minutes");				

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
						'data' => null,                
						'message' => "No hay documentos"
					);    				
				}	
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
					if($decode->sub != $documento->getUsuario() && $decode->rol!="admin"){
						$data = array(
							'status' => 'error',
							'code' => 400,
							'msg' => 'User not admin !!'
						);
					}else{
						$file = new File($this->getParameter('directorio_documentos').'/'.$documento->getRuta());
						//$file = $this->getParameter('directorio_documentos').'/'.$documento->getRuta();
						$data = array(
							'status' => 'success',
							'code' => 200,
							'id' => $id,
							'token' => $authCheck
						);   						
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

		if($file){ return $this->file($file);}
		else{ return $helpers->json($data);}
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
						//Hay que implementar la eliminación del documento de la carpeta
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
}