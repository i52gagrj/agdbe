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

class DescargaController extends Controller {

	public function newAction(Request $request) {
		echo "Hola mundo desde el controlador de Descarga";
		die();
	}	

	/*
	public function todoclienteAction(Request $request) {
		//Devuelve todas las descargas de un cliente
	}
	*/

}