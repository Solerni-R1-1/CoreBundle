<?php

namespace Claroline\CoreBundle\Entity\Mooc;

use Doctrine\ORM\Mapping as ORM;

/**
 * MoocCategory
 *
 * @ORM\Table(name="claro_mooc_category")
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Mooc\MoocCategoryRepository")
 */
class MoocCategory
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;
    
   /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Mooc\Mooc",
     *     mappedBy="categories",
     *     cascade={"persist"}
     * )
     */
    private $moocs;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return mooCategory
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }
    
    public function getMoocs() {
        return $this->moocs;
    }

    public function setMoocs($moocs) {
        $this->moocs = $moocs;
    }
}
