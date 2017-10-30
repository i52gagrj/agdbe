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

	public function newAction(Request $request) {
		echo "Hola mundo desde el controlador de Sesion";
		die();
		// De entrada esto se encargaria de crear la sesión. Pero no se si llamarlo desde aquí, o
		//llamarlo desde el signup
	}	

	/*
	public function todoclienteAction(Request $request) {
		// Devolver todas las sesiones de un determinado cliente, que se pasará como parámetro		
	}
	*/

}