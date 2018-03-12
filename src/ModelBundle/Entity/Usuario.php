<?php

namespace ModelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Usuario
 *
 * @ORM\Table(name="usuario")
 * @ORM\Entity(repositoryClass="ModelBundle\Repository\UsuarioRepository")
 */
class Usuario
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
     * @ORM\Column(name="nombre", type="string", length=255)
     */
    private $nombre;

    /**
     * @var string
     *
     * @ORM\Column(name="rol", type="string", length=20)
     */
    private $rol;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255)
     */
    private $password;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fechaalta", type="datetime")
     */
    private $fechaalta;


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
     * Set nombre
     *
     * @param string $nombre
     *
     * @return Usuario
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre
     *
     * @return string
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set rol
     *
     * @param string $rol
     *
     * @return Usuario
     */
    public function setRol($rol)
    {
        $this->rol = $rol;

        return $this;
    }

    /**
     * Get rol
     *
     * @return string
     */
    public function getRol()
    {
        return $this->rol;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Usuario
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return Usuario
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set fechaalta
     *
     * @param \DateTime $fechaalta
     *
     * @return Usuario
     */
    public function setFechaalta($fechaalta)
    {
        $this->fechaalta = $fechaalta;

        return $this;
    }

    /**
     * Get fechaalta
     *
     * @return \DateTime
     */
    public function getFechaalta()
    {
        return $this->fechaalta;
    }


    //ASOCIACIONES

    /// AUTORELACIÓN CLIENTE - ADMINISTRADOR ///

    /**
     * @ORM\ManyToOne(targetEntity="Usuario", inversedBy="clientes")
     * @ORM\JoinColumn(name="admin_id", referencedColumnName="id")
     */
    private $admin;    

    ///RELACIONES CON DOCUMENTOS, MODELOS, SESIONES, DESCARGAS Y MENSAJES  ///
    ///AUTORELACIÓN ADMINISTRADOR - CLIENTES ///    

    /**
     * @ORM\OneToMany(targetEntity="Documento", mappedBy="usuario")     
     */

    protected $documentos;

    /**
     * @ORM\OneToMany(targetEntity="Modelo", mappedBy="usuario")
     */

    protected $modelos;

    /**
     * @ORM\OneToMany(targetEntity="Sesion", mappedBy="usuario")
     */

    protected $sesiones;

    /**
     * @ORM\OneToMany(targetEntity="Descarga", mappedBy="usuario")
     */

    protected $descargas;

    /**
     * @ORM\OneToMany(targetEntity="Usuario", mappedBy="admin")
     */

    protected $clientes;

    /**
     * @ORM\OneToMany(targetEntity="Mensaje", mappedBy="emisor")
     */

    protected $mensajesemitidos;    

    /**
     * @ORM\OneToMany(targetEntity="Mensaje", mappedBy="receptor")
     */

    protected $mensajesrecibidos;    
 
    public function __construct()
    {
        $this->documentos = new ArrayCollection();
        $this->modelos = new ArrayCollection();
        $this->sesiones = new ArrayCollection();
        $this->descargas = new ArrayCollection();
        $this->clientes = new ArrayCollection();        
        $this->mensajesemitidos = new ArrayCollection();
        $this->mensajesrecibidos = new ArrayCollection();
    }    


    /**
     * Set admin
     *
     * @param \ModelBundle\Entity\Usuario $admin
     *
     * @return Usuario
     */
    public function setAdmin(\ModelBundle\Entity\Usuario $admin = null)
    {
        $this->admin = $admin;

        return $this;
    }

    /**
     * Get admin
     *
     * @return \ModelBundle\Entity\Usuario
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * Add documento
     *
     * @param \ModelBundle\Entity\Documento $documento
     *
     * @return Usuario
     */
    public function addDocumento(\ModelBundle\Entity\Documento $documento)
    {
        $this->documentos[] = $documento;

        return $this;
    }

    /**
     * Remove documento
     *
     * @param \ModelBundle\Entity\Documento $documento
     */
    public function removeDocumento(\ModelBundle\Entity\Documento $documento)
    {
        $this->documentos->removeElement($documento);
    }

    /**
     * Get documentos
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDocumentos()
    {
        return $this->documentos;
    }

    /**
     * Add modelo
     *
     * @param \ModelBundle\Entity\Modelo $modelo
     *
     * @return Usuario
     */
    public function addModelo(\ModelBundle\Entity\Modelo $modelo)
    {
        $this->modelos[] = $modelo;

        return $this;
    }

    /**
     * Remove modelo
     *
     * @param \ModelBundle\Entity\Modelo $modelo
     */
    public function removeModelo(\ModelBundle\Entity\Modelo $modelo)
    {
        $this->modelos->removeElement($modelo);
    }

    /**
     * Get modelos
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getModelos()
    {
        return $this->modelos;
    }

    /**
     * Add sesione
     *
     * @param \ModelBundle\Entity\Sesion $sesione
     *
     * @return Usuario
     */
    public function addSesione(\ModelBundle\Entity\Sesion $sesione)
    {
        $this->sesiones[] = $sesione;

        return $this;
    }

    /**
     * Remove sesione
     *
     * @param \ModelBundle\Entity\Sesion $sesione
     */
    public function removeSesione(\ModelBundle\Entity\Sesion $sesione)
    {
        $this->sesiones->removeElement($sesione);
    }

    /**
     * Get sesiones
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSesiones()
    {
        return $this->sesiones;
    }

    /**
     * Add descarga
     *
     * @param \ModelBundle\Entity\Descarga $descarga
     *
     * @return Usuario
     */
    public function addDescarga(\ModelBundle\Entity\Descarga $descarga)
    {
        $this->descargas[] = $descarga;

        return $this;
    }

    /**
     * Remove descarga
     *
     * @param \ModelBundle\Entity\Descarga $descarga
     */
    public function removeDescarga(\ModelBundle\Entity\Descarga $descarga)
    {
        $this->descargas->removeElement($descarga);
    }

    /**
     * Get descargas
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDescargas()
    {
        return $this->descargas;
    }

    /**
     * Add cliente
     *
     * @param \ModelBundle\Entity\Usuario $cliente
     *
     * @return Usuario
     */
    public function addCliente(\ModelBundle\Entity\Usuario $cliente)
    {
        $this->clientes[] = $cliente;

        return $this;
    }

    /**
     * Remove cliente
     *
     * @param \ModelBundle\Entity\Usuario $cliente
     */
    public function removeCliente(\ModelBundle\Entity\Usuario $cliente)
    {
        $this->clientes->removeElement($cliente);
    }

    /**
     * Get clientes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getClientes()
    {
        return $this->clientes;
    }

    /**
     * Add mensajesemitido
     *
     * @param \ModelBundle\Entity\Mensaje $mensajesemitido
     *
     * @return Usuario
     */
    public function addMensajesemitido(\ModelBundle\Entity\Mensaje $mensajesemitido)
    {
        $this->mensajesemitidos[] = $mensajesemitido;

        return $this;
    }

    /**
     * Remove mensajesemitido
     *
     * @param \ModelBundle\Entity\Mensaje $mensajesemitido
     */
    public function removeMensajesemitido(\ModelBundle\Entity\Mensaje $mensajesemitido)
    {
        $this->mensajesemitidos->removeElement($mensajesemitido);
    }

    /**
     * Get mensajesemitidos
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMensajesemitidos()
    {
        return $this->mensajesemitidos;
    }

    /**
     * Add mensajesrecibido
     *
     * @param \ModelBundle\Entity\Mensaje $mensajesrecibido
     *
     * @return Usuario
     */
    public function addMensajesrecibido(\ModelBundle\Entity\Mensaje $mensajesrecibido)
    {
        $this->mensajesrecibidos[] = $mensajesrecibido;

        return $this;
    }

    /**
     * Remove mensajesrecibido
     *
     * @param \ModelBundle\Entity\Mensaje $mensajesrecibido
     */
    public function removeMensajesrecibido(\ModelBundle\Entity\Mensaje $mensajesrecibido)
    {
        $this->mensajesrecibidos->removeElement($mensajesrecibido);
    }

    /**
     * Get mensajesrecibidos
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMensajesrecibidos()
    {
        return $this->mensajesrecibidos;
    }
}
