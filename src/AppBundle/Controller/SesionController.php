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

	public function listallAction(Request $request) {
		// Devuelve el listado de todas las sesiones de un cliente				
		
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
				$userid = null;				
			}
				
			if($userid){
				//Buscar las sesiones iniciadas por el usuario indicado, ordenadas por fecha
				$em = $this->getDoctrine()->getManager();			

				$dql = "SELECT s.id, s.inicio, s.fin FROM ModelBundle:Sesion s "
                ."WHERE s.usuario = $userid "
				."ORDER BY s.inicio DESC";

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
						'id' => $userid,
						'token' => $authCheck,    
						'data' => null,                
						'message' => "No hay sesiones"
					);    				
				}	
			}else{
				$data = array(
					'status' => 'error',
					'code' => 400,
					'token' => $authCheck,
					'data' => null,
					'msg' => 'id not provided !!'
				); 					
			}	

		}

		return $helpers->json($data);	
	}	

}