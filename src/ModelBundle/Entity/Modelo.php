<?php

namespace ModelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Modelo
 *
 * @ORM\Table(name="modelo")
 * @ORM\Entity(repositoryClass="ModelBundle\Repository\ModeloRepository")
 */
class Modelo
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="descripcion", type="string", length=255)
     */
    private $descripcion;

    /**
     * @var string
     *
     * @ORM\Column(name="codigo", type="string", length=6)
     */
    private $codigo;

    /**
     * @var int
     *
     * @ORM\Column(name="trimestre", type="integer")
     */
    private $trimestre;

    /**
     * @var int
     *
     * @ORM\Column(name="ejercicio", type="integer")
     */
    private $ejercicio;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fechahora", type="datetime")
     */
    private $fechahora;    

    /**
     * @var string
     *
     * @ORM\Column(name="tipo", type="string", length=6)
     */
    private $tipo;

    // /**
    // * @var string
    // *
    // * @ORM\Column(name="contenido", type="blob")
    // */
    //private $contenido;

    /**
     * @var string
     *
     * @ORM\Column(name="ruta", type="string", length=255)
     */
    private $ruta;    

    /**
     * @var int
     *
     * @ORM\Column(name="usuario", type="integer")
     */
    private $usuario;       

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     *
     * @return Modelo
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    /**
     * Get descripcion
     *
     * @return string
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Set codigo
     *
     * @param string $codigo
     *
     * @return Modelo
     */
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;

        return $this;
    }

    /**
     * Get codigo
     *
     * @return string
     */
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * Set trimestre
     *
     * @param integer $trimestre
     *
     * @return Modelo
     */
    public function setTrimestre($trimestre)
    {
        $this->trimestre = $trimestre;

        return $this;
    }

    /**
     * Get trimestre
     *
     * @return int
     */
    public function getTrimestre()
    {
        return $this->trimestre;
    }

    /**
     * Set ejercicio
     *
     * @param integer $ejercicio
     *
     * @return Modelo
     */
    public function setEjercicio($ejercicio)
    {
        $this->ejercicio = $ejercicio;

        return $this;
    }

    /**
     * Get ejercicio
     *
     * @return int
     */
    public function getEjercicio()
    {
        return $this->ejercicio;
    }

    /**
     * Set fechahora
     *
     * @param \DateTime $fechahora
     *
     * @return Documento
     */
    public function setFechahora($fechahora)
    {
        $this->fechahora = $fechahora;

        return $this;
    }

    /**
     * Get fechahora
     *
     * @return \DateTime
     */
    public function getFechahora()
    {
        return $this->fechahora;
    }

    /**
     * Set tipo
     *
     * @param string $tipo
     *
     * @return Modelo
     */
    public function setTipo($tipo)
    {
        $this->tipo = $tipo;

        return $this;
    }

    /**
     * Get tipo
     *
     * @return string
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * Set ruta
     *
     * @param string $ruta
     *
     * @return Modelo
     */
    public function setRuta($ruta)
    {
        $this->ruta = $ruta;

        return $this;
    }

    /**
     * Get ruta
     *
     * @return string
     */
    public function getRuta()
    {
        return $this->ruta;
    }

    /**
     * Set usuario
     *
     * @param int $usuario
     *
     * @return Modelo
     */
    public function setUsuario($usuario)
    {
        $this->usuario = $usuario;

        return $this;
    }

    /**
     * Get usuario
     *
     * @return int
     */
    public function getUsuario()
    {
        return $this->usuario;
    }    

}
