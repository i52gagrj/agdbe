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

	public function newAction(Request $request) {
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);

        $json = $request->get('json', null);
        $params = json_decode($json);

        $data = array(
            'status' => 'error',
            'code' => 400,
            'msg' => 'User not created !!'
        );    

        if($json != null)
        {        	
        	$fechaalta = new \Datetime("now");
        	$rol = (isset($params->rol)) ? $params->rol : null;
        	$nombre = (isset($params->nombre)) ? $params->nombre : null;
        	$email= (isset($params->email)) ? $params->email : null;
        	$password = (isset($params->password)) ? $params->password : null;
        }


        $emailConstraint = new Assert\Email();
        $emailConstraint->message = "This email is not valid!!";
        $validate_email = $this->get("validator")->validate($email, $emailConstraint);

        if($email !=null && count($validate_email) == 0 && $password != null && $nombre != null) 
        {

        	$usuario = new Usuario();
        	$usuario->setNombre($nombre);
        	$usuario->setRol($rol);
        	$usuario->setFechaalta($fechaalta);
        	$usuario->setEmail($email);

        	$pwd = hash('sha256', $password);
        	$usuario->setPassword($pwd);

        	$em = $this->getDoctrine()->getManager();
        	$isset_usuario = $em->getRepository('ModelBundle:Usuario')->findBy(array("email" => $email));

        	if(count($isset_usuario) == 0)
        	{
        		$em->persist($usuario);
        		$em->flush();

		        $data = array(
		            'status' => 'Success',
		            'code' => 200,
		            'msg' => 'New user created !!',
		            'usuario' => $usuario
		        );        		
        	}
        	else
        	{
		        $data = array(
		            'status' => 'error',
		            'code' => 400,
		            'msg' => 'User not created, duplicated !!'
		        );         		
        	}

        }

        return $helpers->json($data);
	}


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
            if($json != null)
            {                           
                $rol = (isset($params->rol)) ? $params->rol : null;
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
            if($rol != null)    $usuario->setRol($rol);                         
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

    /*
    public function returnallclientsAction(Request $request) 
    {
        
    }

    public function returnoneclientAction(Request $request) 
    {
        
    }    
    */

}
