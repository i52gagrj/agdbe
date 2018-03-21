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


    //ASOCIACIONES

    ///RELACIONES CON USUARIO Y MODELO///

    /**
     * @ORM\ManyToOne(targetEntity="Usuario", inversedBy="descargas")
     * @ORM\JoinColumn(name="usuario_id", referencedColumnName="id")
     */
    private $usuario;

    /**
     * @ORM\ManyToOne(targetEntity="Modelo", inversedBy="descargas")
     * @ORM\JoinColumn(name="modelo_id", referencedColumnName="id")
     */
    private $modelo;  

    /**
     * Set usuario
     *
     * @param \ModelBundle\Entity\Usuario $usuario
     *
     * @return Descarga
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

    /**
     * Set modelo
     *
     * @param \ModelBundle\Entity\Modelo $modelo
     *
     * @return Descarga
     */
    public function setModelo(\ModelBundle\Entity\Modelo $modelo = null)
    {
        $this->modelo = $modelo;

        return $this;
    }

    /**
     * Get modelo
     *
     * @return \ModelBundle\Entity\Modelo
     */
    public function getModelo()
    {
        return $this->modelo;
    }
}
