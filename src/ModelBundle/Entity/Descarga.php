<?php

namespace ModelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Descarga
 *
 * @ORM\Table(name="descarga")
 * @ORM\Entity(repositoryClass="ModelBundle\Repository\DescargaRepository")
 */
class Descarga
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
     * @var \DateTime
     *
     * @ORM\Column(name="fechahora", type="datetime")
     */
    private $fechahora;

    /**
     * @var int
     *
     * @ORM\Column(name="usuario", type="integer")
     */
    private $usuario;  

    /**
     * @var int
     *
     * @ORM\Column(name="modelo", type="integer")
     */
    private $modelo;      

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
     * Set fechahora
     *
     * @param \DateTime $fechahora
     *
     * @return Descarga
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
     * Set usuario
     *
     * @param int $usuario
     *
     * @return Descarga
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

    /**
     * Set modelo
     *
     * @param int $modelo
     *
     * @return Descarga
     */
    public function setModelo($modelo)
    {
        $this->modelo = $modelo;

        return $this;
    }

    /**
     * Get modelo
     *
     * @return int
     */
    public function getModelo()
    {
        return $this->modelo;
    }    
}
