<?php
namespace AppBundle\Services;

use Firebase\JWT\JWT;
use ModelBundle\Entity\Sesion;

class JwtAuth 
{
	public $manager;
	public $key;

	public function __construct($manager)
	{
		$this->manager = $manager;
		$this->key='estaeslaclavesecreta19710622';
	}

	public function signup($email, $password)
	{
		$user = $this->manager->getRepository('ModelBundle:Usuario')->findOneBy(array(
			"email" => $email,
			"password" => $password
		));

		$signup = false;

		if(is_object($user)) $signup = true;
		//Iniciar sesión. Devolver en el token el id de la sesion
		

		if($signup)
		{
			//Generar sesion 
			$sesionid=0;

			$sesion = new Sesion;
			$sesion->setInicio(new \Datetime("now"));
			$sesion->setFin(new \Datetime("+15 minutes"));
			$sesion->setUsuario($user->getId());

			$this->manager->persist($sesion);	
			$this->manager->flush();

			$sesionid = $sesion->getId();

			//GENERAR TOKEN JWT

			$token = array(
				"sub" => $user->getId(),
				"email" => $user->getEmail(),
				"nombre" => $user->getNombre(),
				"idsesion" => $sesionid,
				"rol" => $user->getRol(),
				"iat" => time(),
				"exp" => time() + (90000)
			);	

			$jwt = JWT::encode($token, $this->key, 'HS256');
			$data = $jwt;			
		}
		else 
		{
			$data = array(
				'status' => 'error',
				'data' => 'Login failed!'
			);
		}

		return $data;
	}


    public function checkToken($jwt)
    {
    	$auth = false;
        try{
            $decoded = JWT::decode($jwt, $this->key, array('HS256'));
        }catch(\UnexpectedValueException $e){
        	$auth = false;	
        }catch(\DomainException $e){
        	$auth = false;	
        }

        if(isset($decoded) && is_object($decoded) && isset($decoded->sub)){
        	//Generar un nuevo jwt con otros quince minutos más
        	
        	$token = array(
				"sub" => $decoded->sub,
				"email" => $decoded->email,
				"nombre" => $decoded->nombre,
				"idsesion" => $decoded->idsesion,
				"rol" => $decoded->rol,
				"iat" => time(),
				"exp" => time() + (90000)
			);	
			$auth = JWT::encode($token, $this->key, 'HS256');

        	//Modificar sesión para añadirle quince minutos más al tiempo
        	//Recuperar sesion desde $decoded->idsesion
        	//Modificar end para añadirle el tiempo actual +quince minutos
        	//Guardar la sesion modificada
			$em = $this->manager;
			$sesion = $em->getRepository('ModelBundle:Sesion')->find($decoded->idsesion);
			$sesion->setFin(new \Datetime("+15 minutes"));
            $em->persist($sesion);
            $em->flush();

        }
        else
        {
        	//Si no es valido el token
        	$auth = false;
        }

       	return $auth;
    }	

    public function decodeToken($jwt)
    {
        $answer = array(
			"status" => "Error",
			"code" => 400,
			"message" => "Error in Token"
		);	    	 
    	try{
            $decoded = JWT::decode($jwt, $this->key, array('HS256'));
            $auth = true;
        }catch(\UnexpectedValueException $e){
        	$auth = false;	
        }catch(\DomainException $e){
        	$auth = false;	
        }
        if($auth){ 
        	return $decoded;
        }	
        else {
        	return $answer;        
        }	
	}	
	
	public function returnUser($id){
		if($id){
			$user = $this->manager->getRepository('ModelBundle:Usuario')->find($id);
			return $user;
		}
		else{
			return null;
		}

	}

    /*
	public function finsesion($data){
		//La clave de la sesión está en el token. Si es válido, hay que modificar la sesión
		//Quizás esto vaya mejor en el checktoken
		//Revisar
	}
	*/
}

