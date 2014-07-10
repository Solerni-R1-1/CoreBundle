<?php

namespace Claroline\CoreBundle\Entity\Mooc;

use Doctrine\ORM\Mapping as ORM;

/**
 * MoocAccessConstraints
 *
 * @ORM\Table(name="claro_mooc_access_constraints")
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Mooc\MoocAccessConstraintsRepository")
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
     *  inversedBy="moocAccessConstraints",
     *  cascade={"persist", "remove"}
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
     *     targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinTable(name="claro_mooc_constraints_to_users")
     */
    private $matchedUsers;
    
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Mooc\Mooc")
     * @ORM\JoinTable(name="claro_mooc_constraints_to_moocs")
     */
    private $moocs;
    
    
    /* GETTERS and SETTERS */
    
    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getMoocOwner() {
        return $this->moocOwner;
    }

    public function getWhitelist() {
        return $this->whitelist;
    }

    public function getPatterns() {
        return $this->patterns;
    }

    public function getMatchedUsers() {
        return $this->matchedUsers;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function setMoocOwner($moocOwner) {
        $this->moocOwner = $moocOwner;
    }

    public function setWhitelist($whitelist) {
        $this->whitelist = $whitelist;
    }

    public function setPatterns($patterns) {
        $this->patterns = $patterns;
    }

    public function setMatchedUsers(\Doctrine\Common\Collections\ArrayCollection $matchedUsers) {
        $this->matchedUsers = $matchedUsers;
    }
    public function getMoocs() {
        return $this->moocs;
    }

    public function setMoocs(\Doctrine\Common\Collections\ArrayCollection $moocs) {
        $this->moocs = $moocs;
    }




}
