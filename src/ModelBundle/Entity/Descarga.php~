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
}

