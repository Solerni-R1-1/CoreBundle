<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Workspace;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\SerializerBundle\Annotation\Type;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Gedmo\Mapping\Annotation as Gedmo;
use Claroline\CoreBundle\Entity\Mooc\Mooc;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\WorkspaceRepository")
 * @ORM\Table(name="claro_workspace")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({
 *     "Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace"
 *         = "Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace",
 *     "Claroline\CoreBundle\Entity\Workspace\AggregatorWorkspace"
 *         = "Claroline\CoreBundle\Entity\Workspace\AggregatorWorkspace"
 * })
 * @DoctrineAssert\UniqueEntity("code")
 */
abstract class AbstractWorkspace
{
    protected static $visitorPrefix = 'ROLE_WS_VISITOR';
    protected static $collaboratorPrefix = 'ROLE_WS_COLLABORATOR';
    protected static $managerPrefix = 'ROLE_WS_MANAGER';
    protected static $customPrefix = 'ROLE_WS_CUSTOM';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(unique=true)
     * @Assert\NotBlank()
     */
    protected $code;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $displayable = false;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode",
     *     mappedBy="workspace"
     * )
     */
    protected $resources;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Event",
     *     mappedBy="workspace",
     *     cascade={"persist"}
     * )
     */
    protected $events;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Tool\OrderedTool",
     *     mappedBy="workspace",
     *     cascade={"persist"}
     * )
     */
    protected $orderedTools;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Role",
     *     mappedBy="workspace",
     *     cascade={"persist"}
     * )
     */
    protected $roles;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinColumn(name="user_id", onDelete="SET NULL")
     */
    protected $creator;

    /**
     * @ORM\Column(unique=true)
     */
    protected $guid;

    /**
     * @ORM\Column(name="self_registration", type="boolean")
     */
    protected $selfRegistration = false;

    /**
     * @ORM\Column(name="self_unregistration", type="boolean")
     */
    protected $selfUnregistration = false;

    /**
     * @ORM\Column(name="creation_date", type="integer", nullable=true)
    */

    protected $creationDate;
    
    /**
     * @var Claroline\CoreBundle\Entity\Mooc\Mooc
     * 
     * @ORM\OneToOne(
     *      targetEntity="Claroline\CoreBundle\Entity\Mooc\Mooc", 
     *      cascade={"persist", "remove"},
     *      mappedBy="workspace"
     * )
     */
    protected $mooc;
    
    /**
     *
     * @var boolean
     */
    protected $isMooc;
    
    /**
     * 
     * @ORM\ManyToMany(
     *      targetEntity="Claroline\CoreBundle\Entity\User",
     *      inversedBy="notifyWorkspaces"
     * )
     * @var Claroline\CoreBundle\Entity\User
     */
    protected $notifyUsers;
    

    public function __construct()
    {
        $this->roles		= new ArrayCollection();
        $this->orderedTools	= new ArrayCollection();
        $this->notifyUsers	= new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = 0;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getDescription() 
    {
        return $this->description;
    }

    public function setDescription($description) 
    {
        $this->description = $description;
    }

    public function getEvents() 
    {
        return $this->events;
    }

    public function getResources()
    {
        return $this->resources;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getOrderedTools()
    {
        return $this->orderedTools;
    }

    public function addOrderedTool(OrderedTool $tool)
    {
        $this->orderedTools->add($tool);
    }

    public function removeOrderedTool(OrderedTool $tool)
    {
        $this->orderedTools->removeElement($tool);
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function getCreator()
    {
        return $this->creator;
    }

    public function setCreator($creator)
    {
        $this->creator = $creator;
    }

    public function setGuid($guid)
    {
        $this->guid = $guid;
    }

    public function getGuid()
    {
        return $this->guid;
    }

    public function setDisplayable($displayable)
    {
        $this->displayable = $displayable;
    }

    public function isDisplayable()
    {
        return $this->displayable;
    }

    public function setSelfRegistration($selfRegistration)
    {
        $this->selfRegistration = $selfRegistration;
    }

    public function getSelfRegistration()
    {
        return $this->selfRegistration;
    }

    public function setSelfUnregistration($selfUnregistration)
    {
        $this->selfUnregistration = $selfUnregistration;
    }

    public function getSelfUnregistration()
    {
        return $this->selfUnregistration;
    }

    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    public function getCreationDate()
    {
        if (is_null($this->creationDate)) {
            return $this->creationDate;

        } else {
            $date = date('d-m-Y H:i', $this->creationDate);

            return (new \Datetime($date));
        }
    }

    /**
     * 
     * @return Mooc
     */
    public function getMooc()
    {
        return $this->mooc;
    }

    public function setMooc( Mooc $mooc )
    {
        $this->mooc = $mooc;
        
        return $this;
    }
    
    public function isMooc() {
        return ( null !== $this->getMooc() );
    }
    
    public function getIsMooc() {
        $this->isMooc();
    }

    public function setIsMooc( $isMooc ) {
        if ( $isMooc && ! $this->getMooc() ) {
            $mooc = new Mooc();
            $mooc->setWorkspace($this);
            $this->setMooc($mooc);
            return true;
        }
    }
    
    public function getAllUsers($filteredRoles = array()) {
    	$users = array();
    	foreach ($this->getRoles() as $role) {
    		foreach ($role->getUsers() as $roleUser) {
    			$users[$roleUser->getId()] = $roleUser;
    		}
    		foreach ($role->getGroups() as $group) {
    			/* @var $group Group */
	    		foreach ($group->getUsers() as $groupUser) {
	    			$users[$groupUser->getId()] = $groupUser;
	    		}
    		}
    	}
    	
    	foreach ($users as $key => $user) {
    		foreach ($user->getRoles(true) as $role) {
    			if (in_array($role, $filteredRoles)) {
    				unset($users[$key]);
    				break;
    			}
    		}
    	}
    	
    	return array_unique($users);
    }

    public function getNotifyUsers() {
    	return $this->notifyUsers;
    }
    
    public function addNotifyUser($user) {
    	if (!$this->notifyUsers->contains($user)) {
    		$this->notifyUsers->add($user);
    	}
    }
    
    public function removeNotifyUser($user) {
    	if ($this->notifyUsers->contains($user)) {
    		$this->notifyUsers->removeElement($user);
    	}
    }
}
