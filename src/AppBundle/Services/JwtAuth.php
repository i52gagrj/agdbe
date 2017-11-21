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

	public function signup($email, $password, $getHash = null)
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

			if($getHash == null){
				$sesion = new Sesion;

				$sesion->setInicio(new \Datetime("now"));
				$sesion->setFin(new \Datetime("+15 minutes"));

				$this->manager->persist($sesion);	
				$this->manager->flush();

				$sesionid = $sesion->getId();
			}


			//GENERAR TOKEN JWT

			$token = array(
				"sub" => $user->getId(),
				"email" => $user->getEmail(),
				"nombre" => $user->getNombre(),
				"idsesion" => $sesionid,
				"iat" => time(),
				"exp" => time() + (900)
			);	

			$jwt = JWT::encode($token, $this->key, 'HS256');

			$decoded = JWT::decode($jwt, $this->key, array('HS256'));

			if($getHash == null)
			{
				$data = $jwt;
			}
			else
			{
				$data = $decoded;
			}
			
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
        	//Modificar sesión para añadirle quince minutos más al tiempo

        	//Generar un nuevo jwt con otros quince minutos más
        	
        	$token = array(
				"sub" => $decoded->sub,
				"email" => $decoded->email,
				"nombre" => $decoded->nombre,
				"idsesion" => $decoded->idsesion,
				"iat" => time(),
				"exp" => time() + (900)
			);	
			$auth = JWT::encode($token, $this->key, 'HS256');
        }
        else
        {
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

    /*
	public function finsesion($data){
		//La clave de la sesión está en el token. Si es válido, hay que modificar la sesión
		//Quizás esto vaya mejor en el checktoken
		//Revisar
	}
	*/
}