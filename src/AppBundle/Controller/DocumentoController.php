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

class DocumentoController extends Controller {

	public function newAction(Request $request) {
		echo "Hola mundo desde el controlador de Documento";
		die();
	}	

	/* 
	public function devolverAction(Request $request) {
		// Devuelve el contenido de un documento por la id
	}
	*/

	/* 
	public function todoAction(Request $request) {
		// Devuelve el listado de todos los documentos de un cliente
	}
	*/

}