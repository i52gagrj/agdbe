<?php
namespace AppBundle\Services;

use Firebase\JWT\JWT;

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
		/*Iniciar sesión. Devolver en el token el id de la sesion*/
		

		if($signup)
		{
			//GENERAR TOKEN JWT
			$token = array(
				"sub" => $user->getId(),
				"email" => $user->getEmail(),
				"nombre" => $user->getNombre(),				
				"iat" => time(),
				"exp" => time() + (24*60*60)
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


    public function checkToken($jwt, $getIdentity = false)
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
        	$auth = true;
        }else{
        	$auth = false;
        }

        if($getIdentity == false) {
        	return $auth;
        }else {
        	return $decoded;
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