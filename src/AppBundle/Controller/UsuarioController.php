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

class UsuarioController extends Controller {
   ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
   // FUNCIÓN listallAction(Request $request)
   // OBJETIVO
   // ENTRADA
   // SALIDA
   //
   ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function newAction(Request $request) {
        //Modificar. Que solo puedan usarlo los administradores, y que solo pueda crear 
        // usuarios clientes de esos administradores
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);

        $token = $request->get('authorization', null);
		$authCheck = $jwt_auth->checkToken($token);	

		$data = array(
			'status' => 'error',
			'code' => 405,
			'msg' => 'Authorization not valid !!'
		); 
		
        if($authCheck)
        {
            $json = $request->get('json', null);
            $params = json_decode($json);
            $decode = $jwt_auth->decodeToken($token);
                    
            if($json != null)
            {        	
                $fechaalta = new \Datetime("now");        	
                $nombre = (isset($params->nombre)) ? $params->nombre : null;
                $email= (isset($params->email)) ? $params->email : null;
                $password = (isset($params->password)) ? $params->password : null;
                
                $emailConstraint = new Assert\Email();
                $emailConstraint->message = "This email is not valid!!";
                $validate_email = $this->get("validator")->validate($email, $emailConstraint);

                if($email !=null && count($validate_email) == 0 && $password != null && $nombre != null) 
                {

                    $usuario = new Usuario();
                    $usuario->setNombre($nombre);
                    $usuario->setIsadmin(false);
                    $usuario->setFechaalta($fechaalta);
                    $usuario->setEmail($email);
                    $usuario->setAdmin($decode->sub);

                    $pwd = hash('sha256', $password);
                    $usuario->setPassword($pwd);

                    $em = $this->getDoctrine()->getManager();
                    $isset_usuario = $em->getRepository('ModelBundle:Usuario')->findBy(array("email" => $email));

                    if(count($isset_usuario) == 0)
                    {
                        $em->persist($usuario);
                        $em->flush();

                        $data = array(
                            'status' => 'success',
                            'code' => 200,
                            'msg' => 'New user created !!',
                            'usuario' => $usuario, 					
                            'token' => $authCheck,
                        );        		
                    }
                    else
                    {
                        $data = array(
                            'status' => 'error',
                            'code' => 400,
                            'msg' => 'User not created, duplicated !!', 					
                            'token' => $authCheck,
                        );         		
                    }

                }else{
                    $data = array(
                        'status' => 'error',
                        'code' => 400,
                        'msg' => 'email, password or nombre not valid!!', 					
						'token' => $authCheck,
                    );                
                }
            }
            else {
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'msg' => 'Json null!!', 					
                    'token' => $authCheck,
                );
            }
        }

        return $helpers->json($data);
	}

   ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
   // FUNCIÓN listallAction(Request $request)
   // OBJETIVO
   // ENTRADA
   // SALIDA
   //
   ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function editAction(Request $request) 
    {
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);
        $token = $request->get('authorization', null);
        $authCheck = $jwt_auth->checkToken($token);
        if($authCheck)
        {
            $em = $this->getDoctrine()->getManager();
            $identity = $jwt_auth->decodeToken($token);
            $usuario = $em->getRepository('ModelBundle:Usuario')->findOneBy(array('id' => $identity->sub));
            $json = $request->get('json', null);
            $params = json_decode($json);
            $data = array(
                'status' => 'error',
                'code' => 400,
                'msg' => 'User not updated !!'
            );               
            $usuario->setIsadmin(false); 
            if($json != null)
            {                                           
                $nombre = (isset($params->nombre)) ? $params->nombre : null;
                $email= (isset($params->email)) ? $params->email : null;
                $password = (isset($params->password)) ? $params->password : null;
            }
            if($email != null) 
            {
                $emailConstraint = new Assert\Email();
                $emailConstraint->message = "This email is not valid!!";
                $validate_email = $this->get("validator")->validate($email, $emailConstraint);              
                if(count($validate_email)) $usuario->setEmail($email); 
            }
            if($nombre != null) $usuario->setNombre($nombre);            
            if($password != null){
                $pwd = hash('sha256', $password);
                $usuario->setPassword($pwd);
            }       
                
            $isset_usuario = $em->getRepository('ModelBundle:Usuario')->findBy(array("email" => $email));
            if(count($isset_usuario) == 0 || $identity->email == $email)
            {
                $em->persist($usuario);
                $em->flush();
                $data = array(
                    'status' => 'Success',
                    'code' => 200,
                    'msg' => 'User updated !!',
                    'usuario' => $usuario
                );              
            }
            else
            {
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'msg' => 'User not updated, duplicated !!'
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

   ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
   // FUNCIÓN listallAction(Request $request)
   // OBJETIVO
   // ENTRADA
   // SALIDA
   //
   ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////    
    public function returnallclientsAction(Request $request) 
    {
        // Devuelve todos los clientes de un administrador dado       

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

            if($decode->isadmin){
				//Buscar los clientes del administrador
				$em = $this->getDoctrine()->getManager();			

				$dql = "SELECT u FROM ModelBundle:Usuario u WHERE u.admin = {$decode->sub}";
				$query = $em->createQuery($dql);		
				$usuarios = $query->getResult();													

				if($usuarios){	
					$data = array(
						'status' => 'success',
						'code' => 200,
						'token' => $authCheck,                    
						'data' => $usuarios
					);    
				}else{
					$data = array(
						'status' => 'success',
						'code' => 200,
						'token' => $authCheck,    
						'data' => null,                
						'message' => "No hay clientes de este administrador"
					);    				
				}
                
            }else{
                $userid = null;
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'msg' => 'User not admin !!'
                ); 
            }		
		}

		return $helpers->json($data);
    }
}
