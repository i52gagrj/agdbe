<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Validator\Constraints as Assert;
use ModelBundle\Entity\Usuario;
use ModelBundle\Entity\Modelo;
use ModelBundle\Entity\Descarga;
use ModelBundle\Entity\Sesion;
use ModelBundle\Entity\Mensaje;
use AppBundle\Services\Helpers;
use AppBundle\Services\JwtAuth;

class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }

    public function loginAction(Request $request)
    {
        $helpers = $this->get(Helpers::class);
        // Recibir json por POST
        $json = $request->get('json', null);
        $data = array(
            'status' => 'error',
            'code' => 401,
            'message' => 'Send json via post!!!'
        );
        if($json != null)
        {
            //Login
            //Convertir json en objeto php
            $params = json_decode($json);
            //Recoger los datos
            $email = (isset($params->email)) ? $params->email : null;
            $password = (isset($params->password)) ? $params->password : null;            
            $emailConstraint = new Assert\Email();
            $emailConstraint->message = "This email is not valid !!";
            $validate_email = $this->get("validator")->validate($email, $emailConstraint);
            
            if($email != null && count($validate_email) == 0 && $password != null )
            {
                $jwt_auth = $this->get(JwtAuth::class);
                $pwd=hash('sha256',$password);
                $signup = $jwt_auth ->signup($email, $pwd);
                //return $this->json($signup);
                return $helpers->json($signup);
            }
            else
            {
                $data = array(
                    'status' => 'error',
                    'code' => 402,
                    'message' => 'Email incorrect'
                ); 
            }        
        }
        return $helpers->json($data);
    }

    public function returnidentityAction(Request $request) {
        // Solo como prueba. Borrar en versión final
        $helpers = $this->get(Helpers::class);        
        $json = $request->get("token", null);

        $data = array(
            'status' => 'error',
            'code' => 400,
            'message' => 'Json Incorrect'
        );

        if($json != null)
        {
            $petition = json_decode($json);            

            $jwt_auth = $this->get(JwtAuth::class);                            
            $identity = $jwt_auth->decodeToken($petition);    

            $data = $identity;
          
        }
     
        $mandar = new Response(json_encode($data));
        $mandar->headers->set('Content-Type', 'application/json');
        return $mandar;

        //return $helpers->json($data);                            
    }

    public function logoutAction(Request $request) {        
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);        

        $token = $request->get('authorization', null);
		$authCheck = $jwt_auth->checkToken($token);
		
		$data = array(
			'status' => 'success',
			'code' => 200,
			'message' => 'Session ended'
		); 
		
        if($authCheck){		
			$jwt_auth->finsesion($token);            
        }
        else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Authorization not valid'
            );             
        }

		return $helpers->json($data);		
    }

    public function returninfoclientAction(Request $request) {
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);

        $token = $request->get('authorization', null);
		$authCheck = $jwt_auth->checkToken($token);
		

		$data = array(
			'status' => 'error',
			'code' => 405,
			'msg' => 'Authorization not valid'
		); 
		
        if($authCheck){		
			$decode = $jwt_auth->decodeToken($token);									
			$userid = $decode->sub;	
            $isadmin = $decode->isadmin;		

            $data = array(
                'status' => 'error',
                'code' => 407,
                'msg' => 'User is admin',
                'isadmin' => $isadmin
            );             

			if($userid and $isadmin==false){				
				$em = $this->getDoctrine()->getManager();			

				$dql = "SELECT count (m.id) "
				."FROM ModelBundle:Modelo m, ModelBundle:Usuario u "
				."WHERE m.usuario = {$userid} "
				."AND m.usuario = u.id "
				."ORDER BY m.fechahora ASC";				

                $query = $em->createQuery($dql);
                
                if($query->getResult()){
                    $models = $query->getResult()[0];
                }else{
                    $models = 0;
                }
                
				$dql = "SELECT count (d.id) "
				."FROM ModelBundle:Documento d "
				."WHERE d.usuario = {$userid} "
				."ORDER BY d.fechahora DESC";				

                $query = $em->createQuery($dql);             

                if($query->getResult()){
                    $documents = $query->getResult()[0];
                }else{
                    $documents = 0;
                }
                
				$dql = "SELECT count (m.id) "
				."FROM ModelBundle:Mensaje m, ModelBundle:Usuario u1, ModelBundle:Usuario u2 "
				."WHERE (m.emisor = $userid OR m.receptor = $userid) "
				."AND m.emisor = u1.id and m.receptor = u2.id "
				."ORDER BY m.fechahora DESC";				

                $query = $em->createQuery($dql);                            

                if($query->getResult()){
                    $messages = $query->getResult()[0];
                }else{
                    $messages = 0;
                }
				
                $data = array(
					'status' => 'success',
					'code' => 200,
					'token' => $authCheck, 
                    'models' => $models,
                    'documents' => $documents,
                    'messages' => $messages
				);    
			}		

		}

		return $helpers->json($data);	
    }

    public function returninfoadminAction(Request $request) {
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);

        $token = $request->get('authorization', null);
		$authCheck = $jwt_auth->checkToken($token);
		

		$data = array(
			'status' => 'error',
			'code' => 405,
			'msg' => 'Authorization not valid'
		); 
		
        if($authCheck){		
			$decode = $jwt_auth->decodeToken($token);
			//$identity = $jwt_auth->returnUser($decode->sub);						
			$userid = $decode->sub;	
            $isadmin = $decode->isadmin;		

            $data = array(
                'status' => 'error',
                'code' => 407,
                'msg' => 'User is client'
            );             

			if($userid and $isadmin){				
				$em = $this->getDoctrine()->getManager();			

				$dql = $dql = "SELECT count (u.id) FROM ModelBundle:Usuario u WHERE u.admin = {$userid}";								

                $query = $em->createQuery($dql);
                
                if($query->getResult()){
                    $clients = $query->getResult()[0];
                }else{
                    $clients = 0;
                }
                
				$dql = "SELECT count (d.id) "
				    ." FROM ModelBundle:Documento d, ModelBundle:Usuario u2" 
					." WHERE d.usuario = u2.id"
					." AND d.usuario in"
					." (SELECT u.id FROM ModelBundle:Usuario u WHERE u.admin = {$userid} )"
					." AND d.visto = false"
					." ORDER BY d.fechahora DESC";				

                $query = $em->createQuery($dql);             

                if($query->getResult()){
                    $documents = $query->getResult()[0];
                }else{
                    $documents = 0;
                }
                
				$dql = "SELECT count (m.id) "
				    ."FROM ModelBundle:Mensaje m, ModelBundle:Usuario u "
                	."WHERE m.receptor = {$userid} AND m.visto = false AND m.emisor = u.id "
					."ORDER BY m.fechahora DESC";				

                $query = $em->createQuery($dql);                            

                if($query->getResult()){
                    $messages = $query->getResult()[0];
                }else{
                    $messages = 0;
                }
				
                $data = array(
					'status' => 'success',
					'code' => 200,
					'token' => $authCheck, 
                    'clients' => $clients,
                    'documents' => $documents,
                    'messages' => $messages
				);    
			}		

		}

		return $helpers->json($data);	
    }
}