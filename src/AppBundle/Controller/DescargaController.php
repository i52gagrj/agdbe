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
		// NO NECESARIA. El almacenamiento de una descarga se produce al realizarse esta, bien de un documento, bien de un modelo
		echo "Hola mundo desde el controlador de Nueva Descarga";
		die();
	}	

	public function listallAction(Request $request) {
		// Esto solo lo podrá ver un administrador: programar acorde
		// Se pasará el id como parametro, ya que el id del token es el del administrador
		echo "Hola mundo desde el controlador de Listado de descargas de un cliente/usuario";
		die();
	}

}