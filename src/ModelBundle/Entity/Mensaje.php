<?php

namespace ModelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Mensaje
 *
 * @ORM\Table(name="mensaje")
 * @ORM\Entity(repositoryClass="ModelBundle\Repository\MensajeRepository")
 */
class Mensaje
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
     * @ORM\Column(name="texto", type="text")
     */
    private $texto;

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
     * Set texto
     *
     * @param string $texto
     *
     * @return Mensaje
     */
    public function setTexto($texto)
    {
        $this->texto = $texto;

        return $this;
    }

    /**
     * Get texto
     *
     * @return string
     */
    public function getTexto()
    {
        return $this->texto;
    }

    /**
     * Set fechahora
     *
     * @param \DateTime $fechahora
     *
     * @return Mensaje
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

    /**
     * @ORM\ManyToOne(targetEntity="Usuario", inversedBy="mensajesemitidos")
     * @ORM\JoinColumn(name="emisor", referencedColumnName="id")
     */
    private $emisor;

    /**
     * @ORM\ManyToOne(targetEntity="Usuario", inversedBy="mensajesrecibidos")
     * @ORM\JoinColumn(name="receptor", referencedColumnName="id")
     */
    private $receptor; 

    /**
     * Set emisor
     *
     * @param \ModelBundle\Entity\Usuario $emisor
     *
     * @return Mensaje
     */
    public function setEmisor(\ModelBundle\Entity\Usuario $emisor = null)
    {
        $this->emisor = $emisor;

        return $this;
    }

    /**
     * Get emisor
     *
     * @return \ModelBundle\Entity\Usuario
     */
    public function getEmisor()
    {
        return $this->emisor;
    }

    /**
     * Set receptor
     *
     * @param \ModelBundle\Entity\Usuario $receptor
     *
     * @return Mensaje
     */
    public function setReceptor(\ModelBundle\Entity\Usuario $receptor = null)
    {
        $this->receptor = $receptor;

        return $this;
    }

    /**
     * Get receptor
     *
     * @return \ModelBundle\Entity\Usuario
     */
    public function getReceptor()
    {
        return $this->receptor;
    }
}
