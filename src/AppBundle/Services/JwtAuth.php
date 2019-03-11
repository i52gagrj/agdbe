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
	// Esta función realiza la autenticación del correo y la clave pasados por el usuario
	{
		$user = $this->manager->getRepository('ModelBundle:Usuario')->findOneBy(array(
			"email" => $email,
			"password" => $password
		));

		$signup = false;		
		$now = new \Datetime();

		if(is_object($user)) $signup = true;
		//Iniciar sesión. Devolver en el token el id de la sesion		

		if($signup)
		{
			// Leer las sesiones del usuario. Si la última tiene un timestamp de fin mayor que la hora actual, 
			// el usuario aún está conectado.
			$uid = $user->getId();

			$dql = "SELECT s FROM ModelBundle:Sesion s "
				."WHERE s.usuario = $uid "
				."ORDER BY s.fin DESC";

			$query = $this->manager->createQuery($dql);	
			$vacio = $query->getResult();

			if(empty($vacio)){
				$query = null;
			}

			// Si ya está conectado el usuario, se crea el array con el código 404
			$data = array(
				'status' => 'error',
				'message' => 'Login failed, user already connected!',
				//'query' => $query,
				//'vacio' => $vacio,
				//'vacio2' => empty($vacio),
				'code' => 404
			);			
			if($query){
				$ultimotiempo = $query->getResult()[0]->getFin();
			}

			if((isset($query) && $ultimotiempo < $now) || (!isset($query))){
				//Generar y almacenar sesion 						
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
					"isadmin" => $user->getIsadmin(),
					"admin" => $user->getAdmin(),
					"iat" => time(),
					"exp" => time() + (900)
				);	

				$jwt = JWT::encode($token, $this->key, 'HS256');

				// Enviar token JWT
				$data = $jwt;			
			}				

		}
		else 
		{
			// Si hay un error en el email o en la clave, se envia el siguiente array	
			$data = array(
				'status' => 'error',
				'message' => 'Login failed, email or password incorrect!',
				'code' => 403/*,
				'user' => $user*/
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
		
		$now = time();

        if(isset($decoded) && is_object($decoded) && isset($decoded->sub) && $decoded->exp > $now ){
			//Generar un nuevo jwt con otros quince minutos más        	
			$token = array(
				"sub" => $decoded->sub,
				"email" => $decoded->email,
				"nombre" => $decoded->nombre,
				"idsesion" => $decoded->idsesion,
				"isadmin" => $decoded->isadmin,
				"admin" => $decoded->admin,
				"iat" => $decoded->iat,
				"exp" => time() + (900)
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

  	public function finsesion($jwt){
    	$auth = false;
        try{
            $decoded = JWT::decode($jwt, $this->key, array('HS256'));
        }catch(\UnexpectedValueException $e){
        	$auth = false;	
        }catch(\DomainException $e){
        	$auth = false;	
		}
		
		$now = time();

		$retorno = null;

        if(isset($decoded) && is_object($decoded) && isset($decoded->sub) && $decoded->exp > $now ){
			$em = $this->manager;
			$sesion = $em->getRepository('ModelBundle:Sesion')->find($decoded->idsesion);
			$sesion->setFin(new \Datetime);
			$em->persist($sesion);
			$em->flush();
		}	
		else{
			$retorno = 1;
		}

       	return $retorno;		
	}
}

