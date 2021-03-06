<?php
namespace AppBundle\Services;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
use Symfony\Component\HttpFoundation\Response;
 
class Helpers
{
	public $manager;

	function __construct($manager)
	{
		$this->manager = $manager;
	}

	public function json($data) 
	{
		$encoders = array("json" => new JsonEncoder());
		//$encoders = array(new JsonEncoder());
		
		//$normalizers = array(new ObjectNormalizer());
		//$normalizers = array(new GetSetMethodNormalizer());
		//$normalizers = array(new PropertyNormalizer());
		//$normalizers = array(new JsonSerializableNormalizer());

		//$normalizer = new JsonSerializableNormalizer();
		$normalizer = new GetSetMethodNormalizer();
		//$normalizer = new ObjectNormalizer();
        $normalizer->setCircularReferenceLimit(1);
        $normalizer->setCircularReferenceHandler(function($object){
            /** @var Media $object */
            return $object->getId();
        }); 
		
		$serializer = new Serializer([$normalizer], $encoders);

		$json = $serializer->serialize($data, 'json');

		$response = new \Symfony\Component\HttpFoundation\Response();
		$response->setContent($json);
		$response->headers->set('Content-Type', 'application/json');

		return $response;

	}

	public function json2($data) 
	{
		$mandar = new Response(json_encode($data));
        $mandar->headers->set('Content-Type', 'application/json');
        return $mandar;	
	}

}