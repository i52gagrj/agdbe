<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Filesystem\Filesystem;
use ModelBundle\Entity\Usuario;
use ModelBundle\Entity\Modelo;
use ModelBundle\Entity\Descarga;
use AppBundle\Services\Helpers;
use AppBundle\Services\JwtAuth;

class ModeloController extends Controller {

	public function newAction(Request $request) {
        $helpers = $this->get(Helpers::class);
		$jwt_auth = $this->get(JwtAuth::class);

		/* NOTA
		Hay que añadir una condición para que solo puedan añadir modelos los usuarios administradores, y no lo puedan hacer los clientes		
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
			$decoded = $jwt_auth->decodeToken($authCheck);  	
			if($decoded->isadmin)
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
					$modelo->setVisto(false);       

					$em = $this->getDoctrine()->getManager();
					$em->persist($modelo);
					$em->flush();   
					
					$data = array(
						'status' => 'success',
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
					'msg' => 'User not admin', 
					'descripcion' => $params->descripcion,
					'token' => $authCheck
				); 	
			}   		
        }

        else 
        {    		
	        $data = array(
	            'status' => 'error',
	            'code' => 405,
	            'msg' => 'Authorization not valid !!'
	        ); 
        }
        
		return $helpers->json($data);
			
	}	

	
	public function listallAction(Request $request) {
		// Devuelve el listado de todos los modelos de un cliente		

		// Si se pasa el usuario como parametro, se devolverán los modelos del usuario (descripción y datos, no ruta)
		// Esto serviria para que los administradores pasen el id de un usario y recuperen sus modelos
		// Si no se pasa, se recupera el usario del token
		// Esta manera servirá para que los usuarios recuperen el listado de sus modelos
		
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
				if($decode->isadmin){
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
				//Buscar los modelos pertenecientes al usuario indicado, ordenados por fecha
				$em = $this->getDoctrine()->getManager();			

				$dql = "SELECT m.id, m.descripcion, m.codigo, m.trimestre, m.ejercicio, m.ruta, m.fechahora, m.tipo, u.nombre as usuario "
				."FROM ModelBundle:Modelo m, ModelBundle:Usuario u "
				."WHERE m.usuario = {$userid} "
				."AND m.usuario = u.id "
				."ORDER BY m.fechahora ASC";

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
						'message' => "No hay modelos"
					);    				
				}	
			}		

		}

		return $helpers->json($data);	
	}	

	 
	public function returnoneAction(Request $request) {		
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
				// Buscar el modelo indicado				
				$em = $this->getDoctrine()->getManager();			
					
				$modelo = $em->getRepository('ModelBundle:Modelo')->find($id);

				if($modelo){
					if($decode->sub != $modelo->getUsuario() && !$decode->isadmin){
						$data = array(
							'status' => 'error',
							'code' => 400,
							'msg' => 'User not admin !!'
						);
					}else{
						
						$file = new File($this->getParameter('directorio_modelos').'/'.$modelo->getRuta());	
						if(!$decode->isadmin) {
							$descarga = new Descarga;

							$descarga->setFechahora(new \Datetime("now"));
							//Habria que eliminar el usuario: ya no se necesita
							$descarga->setUsuario($decode->sub);
							$descarga->setModelo($id);

							$em->persist($descarga);
							$em->flush();			
						}		
					}	
				}else{
					$data = array(
						'status' => 'success',
						'code' => 200,
						'id' => $id,
						'token' => $authCheck,                    
						'message' => "El modelo no existe"
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

}	