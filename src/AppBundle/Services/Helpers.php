<?php
namespace AppBundle\Services;

//use ModelBundle\Entity\Usuario;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
 
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

}