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

class DescargaController extends Controller {

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
						'msg' => 'User not admin or wrong admin'
					); 
				}
			}else{
				$userid = null;
			}
				

			if($userid){
				//Buscar las sesiones iniciadas por el usuario indicado, ordenadas por fecha
				$em = $this->getDoctrine()->getManager();			

				$dql = "SELECT d FROM ModelBundle:Descarga d "
                ."WHERE d.usuario = $userid "
				."ORDER BY d.fechahora DESC";

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
						'message' => "No hay descargas"
					);    				
				}	
			}else{
				$userid = null;
				$data = array(
					'status' => 'error',
					'data' => null,
					'code' => 400,
					'token' => $authCheck,    
					'data' => null,
					'msg' => 'id not provided'
				);				
			}		

		}

		return $helpers->json($data);	
	}	

}