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

/**
 * @ORM\Table(name="claro_analytics_badge_mooc_stats")
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Analytics\AnalyticsBadgeMoocStatsRepository")
 * @DoctrineAssert\UniqueEntity({"badge", "date", "workspace"})
 */
class AnalyticsBadgeMoocStats {

    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

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
     * @var Badge
     *
     * @ORM\ManyToOne(
     *  targetEntity="Claroline\CoreBundle\Entity\Badge\Badge"
     * )
     *
     */
    private $badge;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     *
     */
    private $badgeType;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date")
     */
    private $date;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $nbParticipations;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $nbSuccess;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $nbFail;

    public function __construct() {
    	$this->nbParticipations = 0;
    	$this->nbSuccess = 0;
    	$this->nbFail = 0;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Badge
     */
    public function getBadge()
    {
    	return $this->badge;
    }

    /**
     * @return string
     */
    public function getBadgeType()
    {
    	return $this->badgeType;
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
     * @return int
     */
    public function getNbParticipations()
    {
    	return $this->nbParticipations;
    }

    /**
     * @return int
     */
    public function getNbSuccess()
    {
    	return $this->nbSuccess;
    }

    /**
     * @return int
     */
    public function getNbFail()
    {
    	return $this->nbFail;
    }

    /**
     * @param Badge badge
     *
     * @return AnalyticsBadgeMoocStats
     */
    public function setBadge($badge)
    {
    	$this->badge = $badge;
    
    	return $this;
    }

    /**
     * @param string badgeType
     *
     * @return AnalyticsBadgeMoocStats
     */
    public function setBadgeType($badgeType)
    {
    	$this->badgeType = $badgeType;
    
    	return $this;
    }

    /**
     * @param int nbParticipations
     *
     * @return AnalyticsBadgeMoocStats
     */
    public function setNbParticipations($nbParticipations)
    {
    	$this->nbParticipations = $nbParticipations;
    
    	return $this;
    }

    /**
     * @param int nbSuccess
     *
     * @return AnalyticsBadgeMoocStats
     */
    public function setNbSuccess($nbSuccess)
    {
    	$this->nbSuccess = $nbSuccess;
    
    	return $this;
    }

    /**
     * @param int nbFail
     *
     * @return AnalyticsBadgeMoocStats
     */
    public function setNbFail($nbFail)
    {
    	$this->nbFail = $nbFail;
    
    	return $this;
    }


    /**
     * @param \DateTime $lastName
     *
     * @return AnalyticsBadgeMoocStats
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @param AbstractWorkspace $workspace
     *
     * @return AnalyticsBadgeMoocStats
     */
    public function setWorkspace(AbstractWorkspace $workspace)
    {
        $this->workspace = $workspace;

        return $this;
    }
    
    public function __toString() {
    	return "AnalyticsBadgeMoocStats(".$this->id.")";
    }
}
