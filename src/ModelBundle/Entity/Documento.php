<?php

namespace ModelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Documento
 *
 * @ORM\Table(name="documento")
 * @ORM\Entity(repositoryClass="ModelBundle\Repository\DocumentoRepository")
 */
class Documento
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
     * @var \DateTime
     *
     * @ORM\Column(name="fechahora", type="datetime")
     */
    private $fechahora;

    /**
     * @var string
     *
     * @ORM\Column(name="tipo", type="string", length=5)
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
     * @return Documento
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
     * @return Documento
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

    // /**
    // * Set contenido
    // *
    // * @param string $contenido
    // *
    // * @return Modelo
    // */
    //public function setContenido($contenido)
    //{
    //    $this->contenido = $contenido;
    // 
    //    return $this;
    //}

    // /**
    //  * Get contenido
    //  *
    //  * @return string
    //  */
    // public function getContenido()
    // {
    //     return $this->contenido;
    // }

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


    //ASOCIACIONES

    //Asociación entre Documento y Usuario

    /**
     * @ORM\ManyToOne(targetEntity="Usuario", inversedBy="documentos")
     * @ORM\JoinColumn(name="usuario_id", referencedColumnName="id")
     */
    private $usuario;
    

    /**
     * Set usuario
     *
     * @param \ModelBundle\Entity\Usuario $usuario
     *
     * @return Documento
     */
    public function setUsuario(\ModelBundle\Entity\Usuario $usuario = null)
    {
        $this->usuario = $usuario;

        return $this;
    }

    /**
     * Get usuario
     *
     * @return \ModelBundle\Entity\Usuario
     */
    public function getUsuario()
    {
        return $this->usuario;
    }
}
