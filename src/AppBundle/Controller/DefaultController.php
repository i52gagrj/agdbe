<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Validator\Constraints as Assert;
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
            'data' => 'Send json via post!!!'
        );
        if($json != null)
        {
            //Login
            //Convertir json en objeto php
            $params = json_decode($json);
            //Recoger los datos
            $email = (isset($params->email)) ? $params->email : null;
            $password = (isset($params->password)) ? $params->password : null;
            $getHash = (isset($params->getHash)) ? $params->getHash : null;
            $emailConstraint = new Assert\Email();
            $emailConstraint->message = "This email is not valid !!";
            $validate_email = $this->get("validator")->validate($email, $emailConstraint);
            
            if($email != null && count($validate_email) == 0 && $password != null )
            {
                $jwt_auth = $this->get(JwtAuth::class);
                $pwd=hash('sha256',$password);
                $signup = $jwt_auth ->signup($email, $pwd);
                return $this->json($signup);
                $data = array(
                    'status' => 'success',
                    'data' => 'Email correct',
                    'token' => $signup
                );                 
            }
            else
            {
                $data = array(
                    'status' => 'error',
                    'data' => 'Email incorrect'
                ); 
            }        
        }
        return $helpers->json($data);
    }

    public function returnidentityAction(Request $request) {
        $helpers = $this->get(Helpers::class);
        // Recibir json por POST
        $json = $request->get("token", null);

        $data = array(
            'status' => 'error',
            'code' => 400,
            'data' => 'Json Incorrect'
        );

        if($json != null)
        {
            $petition = json_decode($json);            

            $jwt_auth = $this->get(JwtAuth::class);                            
            $identity = $jwt_auth->decodeToken($petition);    

            $data = $identity;
            
            /*$data = array(
                'status' => 'success',
                'code' => 200,
                'data' => $identity
            );*/            
        }
     
        $mandar = new Response(json_encode($data));
        $mandar->headers->set('Content-Type', 'application/json');
        return $mandar;

        //return $helpers->json($data);                            
    }

    public function pruebasAction(Request $request) {
        $token = $request->get("authorization", null);
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);
        $newtoken = $jwt_auth->checkToken($token);
        if($token && $newtoken){                
            $datos = $jwt_auth->decodeToken($token);   
            $usuario = $jwt_auth->returnUser($datos->sub);        
                
            //echo $datos->sub;
            //die();            

            $json = array(
                'status' => 'success', 
                'users' => $usuario/*,
                'token' => $newtoken*/
            );
        }
        else 
        {
            $json = array(
                'status' => 'error', 
                'code' => 400,
                'users' => 'Authorization not valid'
            );    
        }        
        
        /*$mandar = new Response(json_encode($json));
        $mandar->headers->set('Content-Type', 'application/json');
        return $mandar;*/

        return $helpers->json($json);
    }
}