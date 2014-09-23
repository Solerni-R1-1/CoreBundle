<?php

namespace Claroline\CoreBundle\Entity\Mooc;

use Doctrine\ORM\Mapping as ORM;
use Claroline\CoreBundle\Entity\Mooc\MoocOwner;
use Claroline\CoreBundle\Entity\Mooc\Mooc;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * MoocAccessConstraints
 *
 * @ORM\Table(name="claro_mooc_access_constraints")
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Mooc\MoocAccessConstraintsRepository")
 * @ORM\HasLifecycleCallbacks
 */
class MoocAccessConstraints
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
     * @var Claroline\CoreBundle\Entity\Mooc\MoocOwner
     * 
     * @ORM\ManyToOne(
     *  targetEntity="Claroline\CoreBundle\Entity\Mooc\MoocOwner",
     *  inversedBy="moocAccessConstraints"
     * )
     * 
     */
    private $moocOwner;

    /**
     * @var string
     *
     * @ORM\Column(name="whitelist", type="text", nullable=true)
     */
    private $whitelist;

    /**
     * @var string
     *
     * @ORM\Column(name="patterns", type="text", nullable=true)
     */
    private $patterns;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Mooc\Mooc",
     *     mappedBy="accessConstraints",
     *     cascade={"persist"}
     * )
     */
    private $moocs;

    /**
     * @var SessionsByUsers[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Claroline\CoreBundle\Entity\Mooc\SessionsByUsers", 
     * mappedBy="moocAccessConstraints", 
     * cascade={"all"})
     */
    protected $sessionsByUsers;

    /* GETTERS and SETTERS */

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getMoocOwner()
    {
        return $this->moocOwner;
    }

    public function getWhitelist()
    {
        return $this->whitelist;
    }

    public function getPatterns()
    {
        return $this->patterns;
    }

    public function getMatchedUsers()
    {
        return $this->matchedUsers;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setMoocOwner(MoocOwner $moocOwner)
    {
        $this->moocOwner = $moocOwner;
    }

    public function setWhitelist($whitelist)
    {
        $this->whitelist = $whitelist;
    }

    public function setPatterns($patterns)
    {
        $this->patterns = $patterns;
    }

    public function setMatchedUsers($matchedUsers)
    {
        $this->matchedUsers = $matchedUsers;
    }

    public function getMoocs()
    {
        return $this->moocs;
    }

    public function setMoocs($moocs)
    {
        $this->moocs = $moocs;
    }

    public function getSessionsByUsers()
    {
        return $this->sessionsByUsers;
    }

    public function setSessionsByUsers(\Doctrine\Common\Collections\ArrayCollection $sessionsByUsers)
    {
        $this->sessionsByUsers = $sessionsByUsers;
    }

    
    public function getRoles()
    {
        $roles = new ArrayCollection();
        foreach ($this->getMoocs() as $mooc) {
            foreach ($mooc->getWorkspace()->getRoles() as $role) {
               if (!$roles->contains($role)) {
                    $roles->add($role);
                }
            }
        }
        return $roles;
    }
    
    public function addMooc($mooc) 
    {
        $moocs = $this->getMoocs();
        if (!$moocs->contains($mooc)) {
            $moocs->add($mooc);
            $this->setMoocs($moocs);
        }
    }
    
    public function removeMooc($mooc) 
    {
        $moocs = $this->getMoocs();
        if (!$moocs->contains($mooc)) {
            $moocs->remove($mooc);
            $this->setMoocs($moocs);
        }
    }

}
