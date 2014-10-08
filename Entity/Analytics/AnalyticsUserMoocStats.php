<?php

/*
 * $this file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with $this source code.
 */

namespace Claroline\CoreBundle\Entity\Analytics;

use Claroline\CoreBundle\Entity\Badge\Badge;
use Claroline\CoreBundle\Manager\LocaleManager;
use \Serializable;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Claroline\CoreBundle\Entity\AbstractRoleSubject;
use Claroline\CoreBundle\Entity\Role;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\ExecutionContextInterface;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\User;

/**
 * @ORM\Table(name="claro_analytics_user_mooc_stats")
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Analytics\AnalyticsUserMoocStatsRepository")
 * @DoctrineAssert\UniqueEntity({"user", "workspace", "date"})
 */
class AnalyticsUserMoocStats {

    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var int
     *
     * @ORM\Column()
     */
    protected $nbPublicationsForum;

    /**
     * @var int
     *
     * @ORM\Column()
     */
    protected $nbActivity;

    /**
     * @var AbstractWorkspace
     *
     * @ORM\ManyToOne(
     *  targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     *
     */
    private $user;

    /**
     * @var AbstractWorkspace
     *
     * @ORM\ManyToOne(
     *  targetEntity="Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace"
     * )
     *
     */
    private $workspace;
    

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date")
     */
    private $date;    

    public function __construct()
    {
        $this->nbPublicationsForum	= 0;
        $this->nbActivity 			= 0;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getNbPublicationsForum()
    {
    	return $this->nbPublicationsForum;
    }

    /**
     * @return int
     */
    public function getNbActivity()
    {
    	return $this->nbActivity;
    }

    /**
     * @return User
     */
    public function getUser()
    {
    	return $this->user;
    }

    /**
     * @return AbstractWorkspace
     */
    public function getWorkspace()
    {
        return $this->workspace;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param int $nbPublicationsForum
     *
     * @return AnalyticsUserMoocStats
     */
    public function setNbPublicationsForum($nbPublicationsForum)
    {
        $this->nbPublicationsForum = $nbPublicationsForum;

        return $this;
    }

    /**
     * @param int $nbActivity
     *
     * @return AnalyticsUserMoocStats
     */
    public function setNbActivity($nbActivity)
    {
    	$this->nbActivity = $nbActivity;
    
    	return $this;
    }

    /**
     * @param int $user
     *
     * @return AnalyticsUserMoocStats
     */
    public function setUser(User $user)
    {
    	$this->user = $user;
    
    	return $this;
    }

    /**
     * @param \DateTime $date
     *
     * @return AnalyticsUserMoocStats
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @param AbstractWorkspace $workspace
     *
     * @return AnalyticsUserMoocStats
     */
    public function setWorkspace(AbstractWorkspace $workspace)
    {
        $this->workspace = $workspace;

        return $this;
    }
    
    public function __toString() {
    	return "AnalyticsUserMoocStats(".$this->id.") : ".$this->nbConnections." connections the ".$this->date->format("Y-m-d")." in Workspace ".$this->workspace->getId();
    }
}
