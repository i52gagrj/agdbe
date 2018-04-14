<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Validator\Constraints as Assert;
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
			'code' => 400,
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
				$origen = $identity;
				$destino = (isset($params->destino)) ? $jwt_auth->returnUser($params->destino) : null;
				$texto = (isset($params->texto)) ? $params->texto : null;
				
				if($destino && $texto)
				{
					//Crear objeto mensaje
					$mensaje = new Mensaje();
					
					// Salvar los datos en la entidad mensaje
					$mensaje->setTexto($texto);				
					$mensaje->setFechahora($creacion);
					$mensaje->setEmisor($origen->getId());
					$mensaje->setReceptor($destino->getId());

					// Crear conexion a base de datos
					$em = $this->getDoctrine()->getManager();			

					// Guardar los datos
					$em->persist($mensaje);
					$em->flush();

					$data = array(
						'status' => 'success',
						'code' => 200,
						'token' => $authCheck,
						'msg' => 'Message stored'
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

	public function todoAction(Request $request) {
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
			$identity = $jwt_auth->returnUser($decode->sub);				

			/*
			Buscar los mensajes enviados y recibidos por el usuario identificado, ordenados por fecha
			*/
			$em = $this->getDoctrine()->getManager();			

			$dql = "SELECT m FROM ModelBundle:Mensaje m "
                ."WHERE m.emisor = $decode->sub OR m.receptor = $decode->sub "
				."ORDER BY m.fechahora ASC";

			$query = $em->createQuery($dql);
	
			$mensajes = $query->getResult();

			//FALTARIA PAGINARLOS

			if($mensajes){	
				$data = array(
					'status' => 'success',
					'code' => 200, 
					'token' => $authCheck,                   
					'mensajes' => $mensajes
				);    
			}else{
				$data = array(
					'status' => 'success',
					'code' => 200, 
					'token' => $authCheck,                                       
					'mensajes' => "No hay mensajes"
				);    				
			}			

		}

		return $helpers->json($data);		

	}	

	public function messageuserAction(Request $request) {
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);

		$token = $request->get('authorization', null);
		$id = $request->get('id', null);
		$authCheck = $jwt_auth->checkToken($token);

		$data = array(
			'status' => 'error',
			'code' => 400,
			'msg' => 'Authorization not valid !!'
		); 
		
        if($authCheck){		
			$decode = $jwt_auth->decodeToken($token);
			$identity = $jwt_auth->returnUser($decode->sub);				

			/*
			Buscar los mensajes enviados y recibidos por el usuario identificado, ordenados por fecha
			*/
			$em = $this->getDoctrine()->getManager();			

			$dql = "SELECT m FROM ModelBundle:Mensaje m "
                ."WHERE m.emisor = $id OR m.receptor = $id "
				."ORDER BY m.fechahora ASC";

			$query = $em->createQuery($dql);
	
			$mensajes = $query->getResult();

			//FALTARIA PAGINARLOS

			if($mensajes){	
				$data = array(
					'status' => 'success',
					'code' => 200, 
					'token' => $authCheck,                   
					'mensajes' => $mensajes
				);    
			}else{
				$data = array(
					'status' => 'success',
					'code' => 200, 
					'token' => $authCheck,                                       
					'mensajes' => "No hay mensajes"
				);    				
			}			

		}

		return $helpers->json($data);		

	}		
}