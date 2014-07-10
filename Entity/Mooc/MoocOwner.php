<?php

namespace Claroline\CoreBundle\Entity\Mooc;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * MoocOwner
 *
 * @ORM\Table(name="claro_mooc_owner")
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Mooc\MoocOwnerRepository")
 * @ORM\HasLifecycleCallbacks
 */
class MoocOwner
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="logo_path", type="string", length=255, nullable=true)
     */
    private $logoPath;
    

    /**
     * @var string
     *
     * @ORM\Column(name="dressing_path", type="string", length=255, nullable=true)
     */
    private $dressingPath;
    
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * 
     * @ORM\OneToMany(
     * targetEntity="Claroline\CoreBundle\Entity\Mooc\Mooc",
     * mappedBy="owner",
     * cascade={"persist", "remove"}
     * )
     * 
     */
    private $moocs;
    
     /**
     *
     * @Assert\File(
     *     maxSize = "2048k"
     * )
     */
    protected $logoFile;
    
     /**
     *
     * @Assert\File(
     *     maxSize = "2048k"
     * )
     */
    protected $dressingFile;
    
    /* GETTERS/SETTERS */

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getLogoPath() {
        return $this->logoPath;
    }

    public function getDressingPath() {
        return $this->dressingPath;
    }

    public function getLogoFile() {
        return $this->logoFile;
    }

    public function getDressingFile() {
        return $this->dressingFile;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function setLogoPath($logoPath) {
        $this->logoPath = $logoPath;
    }

    public function setDressingPath($dressingPath) {
        $this->dressingPath = $dressingPath;
    }

    public function setLogoFile($logoFile) {
        $this->logoFile = $logoFile;
    }

    public function setDressingFile($dressingFile) {
        $this->dressingFile = $dressingFile;
    }
    public function getMoocs() {
        return $this->moocs;
    }
    public function setMoocs(\Doctrine\Common\Collections\ArrayCollection $moocs) {
        $this->moocs = $moocs;
    }
    
    /* FILE UPLOADS */
            
    /**
     * @return string
     */
    protected function getUploadDir()
    {
        return sprintf("uploads%sowners", DIRECTORY_SEPARATOR);
    }

     /**
     * @throws \Exception
     * @return string
     */
    protected function getUploadRootDir()
    {
        $ds = DIRECTORY_SEPARATOR;

        $uploadRootDir = sprintf(
            '%s%s..%s..%s..%s..%s..%s..%s..%sweb%s%s',
            __DIR__, $ds, $ds, $ds, $ds, $ds, $ds, $ds, $ds, $ds, $this->getUploadDir()
        );

        if ( ! realpath($uploadRootDir) ) {
            try {
                mkdir( $uploadRootDir );
            } catch( \Symfony\Component\Filesystem\Exception\IOException $ex ) {
                throw new \Exception(
                    sprintf(
                        "Cannot create '%s' folder %s",
                        $uploadRootDir,
                        $ex->getMessage()
                    )
                );
            }
        }
        
        if (false === realpath($uploadRootDir)) {
            throw new \Exception(
                sprintf(
                    "Invalid upload root dir '%s'for uploading owner images.",
                    $uploadRootDir
                )
            );
        }

        return realpath($uploadRootDir);
    }
    
     /**
     * @return null|string
     */
    public function getLogoAbsolutePath()
    {
        return (null === $this->logoPath) ? null : $this->getUploadRootDir() . DIRECTORY_SEPARATOR . $this->logoPath;
    }

    /**
     * @return null|string
     */
    public function getLogoWebPath()
    {
        return (null === $this->logoPath) ? null : $this->getUploadDir() . DIRECTORY_SEPARATOR . $this->logoPath;
    }
    
     /**
     * @return null|string
     */
    public function getDressingAbsolutePath()
    {
        return (null === $this->dressingPath) ? null : $this->getUploadRootDir() . DIRECTORY_SEPARATOR . $this->dressingPath;
    }

    /**
     * @return null|string
     */
    public function getDressingWebPath()
    {
        return (null === $this->dressingPath) ? null : $this->getUploadDir() . DIRECTORY_SEPARATOR . $this->dressingPath;
    }
    
    
    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUploadImages()
    {
        if (null !== $this->logoFile) {
            // faites ce que vous voulez pour générer un nom unique
            $this->logoPath = sha1(uniqid(mt_rand(), true)).'.'.$this->logoFile->guessExtension();
        }
        if (null !== $this->dressingFile) {
            // faites ce que vous voulez pour générer un nom unique
            $this->dressingPath = sha1(uniqid(mt_rand(), true)).'.'.$this->dressingFile->guessExtension();
        }
    }
    
    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function uploadImages()
    {
        if ( null !== $this->logoFile) {
            $this->logoFile->move($this->getUploadRootDir(), $this->logoPath);
            unset($this->logoFile);
        }
        if ( null !== $this->dressingFile) {
            $this->dressingFile->move($this->getUploadRootDir(), $this->dressingPath);
            unset($this->dressingFile);
        }
    }
 
}
