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
 * @ORM\Table(name="claro_analytics_mooc_stats")
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Analytics\AnalyticsMoocStatsRepository")
 * @DoctrineAssert\UniqueEntity({"date", "workspace"})
 */
class AnalyticsMoocStats {

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
    protected $nbConnections;

    /**
     * @var int
     *
     * @ORM\Column()
     */
    protected $nbSubscriptions;

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
        $this->nbConnections	= 0;
        $this->nbSubscriptions	= 0;
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
    public function getNbConnections()
    {
    	return $this->nbConnections;
    }

    /**
     * @return int
     */
    public function getNbSubscriptions()
    {
    	return $this->nbSubscriptions;
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
     * @param int $nbConnections
     *
     * @return AnalyticsMoocStats
     */
    public function setNbConnections($nbConnections)
    {
        $this->nbConnections = $nbConnections;

        return $this;
    }

    /**
     * @param int $nbSubscriptions
     *
     * @return AnalyticsMoocStats
     */
    public function setNbSubscriptions($nbSubscriptions)
    {
    	$this->nbSubscriptions = $nbSubscriptions;
    
    	return $this;
    }

    /**
     * @param \DateTime $lastName
     *
     * @return AnalyticsMoocStats
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @param AbstractWorkspace $workspace
     *
     * @return AnalyticsMoocStats
     */
    public function setWorkspace(AbstractWorkspace $workspace)
    {
        $this->workspace = $workspace;

        return $this;
    }
    
    public function __toString() {
    	return "AnalyticsMoocConnection(".$this->id.") : ".$this->nbConnections." connections the ".$this->date->format("Y-m-d")." in Workspace ".$this->workspace->getId();
    }
}
