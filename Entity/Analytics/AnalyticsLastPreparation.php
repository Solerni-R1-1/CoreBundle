<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
 * @ORM\Table(name="claro_analytics_last_preparation")
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Analytics\AnalyticsLastPreparationRepository")
 */
class AnalyticsLastPreparation {

    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column()
     */
    protected $classname;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="datetime", type="datetime", unique=true)
     */
    private $datetime;    

    public function __construct()
    {
    	
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getClassname()
    {
        return $this->classname;
    }


    /**
     * @return \DateTime
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * @param string $classname
     *
     * @return AnalyticsLastPreparation
     */
    public function setClassname($classname)
    {
        $this->classname = $classname;

        return $this;
    }

    /**
     * @param \DateTime $datetime
     *
     * @return AnalyticsLastPreparation
     */
    public function setDatetime(\DateTime $datetime)
    {
        $this->datetime = $datetime;

        return $this;
    }
    
    public function __toString() {
    	return "AnalyticsLastPreparation(".$this->id.") : Last preparation for ".$this->classname." the ".$this->datetime->format("Y-m-d H:i");
    }
}
