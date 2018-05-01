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

class SesionController extends Controller {

	public function newAction(Request $request) {
		//NO NECESARIA. La sesión se crea y almacena al realizarse el login
		echo "Hola mundo desde el controlador de Sesion";
		die();
	}	

	public function allsessionsAction(Request $request) {
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);

		$token = $request->get('authorization', null);
		$id = $request->get('id', null);
		$authCheck = $jwt_auth->checkToken($token);

		$data = array(
			'status' => 'error',
			'code' => 405,
			'msg' => 'Authorization not valid !!'
		); 
		
        if($authCheck){		
			$decode = $jwt_auth->decodeToken($token);
			//$identity = $jwt_auth->returnUser($decode->sub);				

			/*
			Buscar los mensajes enviados y recibidos por el usuario identificado, ordenados por fecha
			*/
			$em = $this->getDoctrine()->getManager();			

			$dql = "SELECT s FROM ModelBundle:Sesion s "
                ."WHERE s.usuario = $id "
				."ORDER BY s.inicio ASC";

			$query = $em->createQuery($dql);
	
			$sesiones = $query->getResult();

			//FALTARIA PAGINARLOS

			if($sesiones){	
				$data = array(
					'status' => 'success',
					'code' => 200, 
					'token' => $authCheck,                   
					'sesiones' => $sesiones
				);    
			}else{
				$data = array(
					'status' => 'success',
					'code' => 200, 
					'token' => $authCheck,                                       
					'sesiones' => "No hay sesiones"
				);    				
			}			

		}

		return $helpers->json($data);		

	}

}