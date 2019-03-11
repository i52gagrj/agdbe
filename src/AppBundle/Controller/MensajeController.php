<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Tools\Pagination\Paginator;
use ModelBundle\Entity\Mensaje;
use AppBundle\Services\Helpers;
use AppBundle\Services\JwtAuth;

class MensajeController extends Controller {

	public function newAction(Request $request) {
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
			$json = $request->get('json', null);			

			if($json != null)
			{
				$params = json_decode($json);
				$creacion = new \Datetime('now');
				$emisor = $identity->getId();
				$receptor = (isset($params->receptor)) ? $params->receptor : null;
				$texto = (isset($params->texto)) ? $params->texto : null;
				
				if($receptor && $texto)
				{
					//Crear objeto mensaje
					$mensaje = new Mensaje();
					
					// Salvar los datos en la entidad mensaje
					$mensaje->setTexto($texto);				
					$mensaje->setFechahora($creacion);
					$mensaje->setEmisor($emisor);
					$mensaje->setReceptor($receptor);
					$mensaje->setVisto(false);

					// Crear conexion a base de datos
					$em = $this->getDoctrine()->getManager();			

					// Guardar los datos
					$em->persist($mensaje);
					$em->flush();

					$data = array(
						'status' => 'success',
						'code' => 200,
						'token' => $authCheck,
						'mensaje' => $mensaje
					); 

				}
				else
				{
					$data = array(
						'status' => 'error',
						'code' => 400,
						'token' => $authCheck,
						'msg' => 'Wrong data'
					); 					
				}

			}
			else
			{
				$data = array(
					'status' => 'error',
					'code' => 400,
					'token' => $authCheck,
					'msg' => 'Params failed'
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
			'code' => 400,
			'msg' => 'Id not valid !!'
		); 
		
        if($authCheck){		
			$decode = $jwt_auth->decodeToken($token);
			//$identity = $jwt_auth->returnUser($decode->sub);						
			$id = $request->get('id', null);

			if($id){
				if($decode->isadmin){
					$userid = $id;
				}else{
					$userid = null;
					$data = array(
						'status' => 'error',
						'code' => 400,
						'token' => $authCheck,
						'msg' => 'User not admin !!'
					); 
				}
			}else{
				if(!$decode->isadmin){
					$userid = $decode->sub;
				}else{
					$userid = null;
					$data = array(
						'status' => 'error',
						'code' => 400,
						'token' => $authCheck,
						'data' => null,
						'msg' => 'user admin, id not provided !!'
					); 
				}
			}
				
			if($userid){
				//Buscar los mensajes pertenecientes al usuario indicado, ordenados por fecha
				$em = $this->getDoctrine()->getManager();			

				$dql = "SELECT m.id, m.texto, m.fechahora, m.visto, u1.nombre as emisor, u2.nombre as receptor "
					."FROM ModelBundle:Mensaje m, ModelBundle:Usuario u1, ModelBundle:Usuario u2 "
					."WHERE (m.emisor = $userid OR m.receptor = $userid) "
					."AND m.emisor = u1.id and m.receptor = u2.id "
					."ORDER BY m.fechahora DESC";

				$query = $em->createQuery($dql);

				//Paginarlos
				/*$page = $request->query->getInt('page', 1);
				$paginator = $this->get('knp_paginator');
				$items_per_page = 10;
				$pagination = $paginator->paginate($query, $page, $items_per_page);
				$total_items_count = $pagination->getTotalItemCount();*/			
		
				$mensajes = $query->getResult();				

				if($mensajes){	
					$data = array(
						'status' => 'success',
						'code' => 200,
						'token' => $authCheck,                    
						/*'total_items_count' => $total_items_count,
						'page_actual' => $page,
						'items_per_page' => $items_per_page,
						'total_pages' => ceil($total_items_count / $items_per_page),*/
						'data' => $mensajes
					);    
				}else{
					$data = array(
						'status' => 'success',
						'code' => 200,
						'id' => $userid,
						'token' => $authCheck,                    
						'message' => "No hay mensajes"
					);    				
				}	
			}

		}else {
			$data = array(
				'status' => 'error',
				'code' => 405,
				'msg' => 'Authorization not valid !!'
			); 
		}

		return $helpers->json($data);	
	}	

	public function listnewAction(Request $request) {
		
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

			if($decode){				
				$em = $this->getDoctrine()->getManager();			

				/*$dql = "SELECT m FROM ModelBundle:Mensaje m "
                	."WHERE m.receptor = $decode->sub AND m.visto = false "
					."ORDER BY m.fechahora DESC";*/

				$dql = "SELECT m.id, m.texto, m.fechahora, m.emisor as idem, u.nombre as emisor, m.visto "
					."FROM ModelBundle:Mensaje m, ModelBundle:Usuario u "
                	."WHERE m.receptor = $decode->sub AND m.visto = false AND m.emisor = u.id "
					."ORDER BY m.fechahora DESC";

				$query = $em->createQuery($dql);

				//Paginarlos
				/*$page = $request->query->getInt('page', 1);
				$paginator = $this->get('knp_paginator');
				$items_per_page = 10;
				$pagination = $paginator->paginate($query, $page, $items_per_page);
				$total_items_count = $pagination->getTotalItemCount();*/			
		
				$mensajes = $query->getResult();				

				if($mensajes){	
					$data = array(
						'status' => 'success',
						'code' => 200,
						'token' => $authCheck,                    
						/*'total_items_count' => $total_items_count,
						'page_actual' => $page,
						'items_per_page' => $items_per_page,
						'total_pages' => ceil($total_items_count / $items_per_page),*/
						'data' => $mensajes
					);    
				}else{
					$data = array(
						'status' => 'success',
						'code' => 200,
						'token' => $authCheck,                    
						'message' => "No hay mensajes"
					);    				
				}	
			}		

		}

		return $helpers->json($data);	
	}		

	public function messageuserAction(Request $request) {
        $helpers = $this->get(Helpers::class);
		$em = $this->getDoctrine()->getManager();					
		$id = 1;			
		$dql = "SELECT u1.nombre as emisor, u2.nombre as receptor, m.texto, m.visto, m.id "
			."FROM ModelBundle:Mensaje m, ModelBundle:Usuario u1, ModelBundle:Usuario u2 "
			."WHERE (m.receptor = $id or m.emisor = $id) "
			."AND m.emisor = u1.id AND m.receptor = u2.id "
			."AND m.visto = false "
			."ORDER BY m.fechahora DESC";			
		$query = $em->createQuery($dql);			

		if($query->getResult()){	
			$data = array(
				'status' => 'success',
				'code' => 200,
				'data' => $query->getResult()									
			);    
		}else{
			$data = array(
				'status' => 'success',
				'code' => 200,
				'mensajes' => "No hay mensajes"
			);    				
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
				$mensaje = $em->getRepository('ModelBundle:Mensaje')->find($id);

				if($mensaje){
					if($mensaje->getEmisor() == $decode->sub || $mensaje->getReceptor() == $decode->sub){
						$mensaje->setVisto($nuevoestado);
						$em->persist($mensaje);
						$em->flush();
						$data = array(
							'status' => 'success',
							'code' => 200,
							'token' => $authCheck,  
							'nuevoestado' => $nuevoestado,                  
							'data' => $mensaje,
							'msg' => 'Mensaje actualizado'
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
						'msg' => 'No existe el mensaje'
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