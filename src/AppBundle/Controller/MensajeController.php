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

class MensajeController extends Controller {

	public function newAction(Request $request) {
		echo "Hola mundo desde el controlador de Mensaje";
		die();
	}	

	public function todoAction(Request $request) {
		//Mostrar todos los mensajes de un usuario, que se pasa como parametro
		echo "Hola mundo desde el envio de todos los mensajes";
		die();		
	}

}