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
 * @ORM\Table(name="claro_analytics_hourly_mooc_stats")
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Analytics\AnalyticsHourlyMoocStatsRepository")
 * @DoctrineAssert\UniqueEntity({"date", "workspace"})
 */
class AnalyticsHourlyMoocStats {

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
    protected $action;

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
     * @var int
     *
     * @ORM\Column()
     */
    private $h0;
    
    /**
     * @var int
     *
     * @ORM\Column()
     */
    private $h1;
    
    /**
     * @var int
     *
     * @ORM\Column()
     */
    private $h2;
    
    /**
     * @var int
     *
     * @ORM\Column()
     */
    private $h3;
    
    /**
     * @var int
     *
     * @ORM\Column()
     */
    private $h4;
    
    /**
     * @var int
     *
     * @ORM\Column()
     */
    private $h5;
    
    /**
     * @var int
     *
     * @ORM\Column()
     */
    private $h6;
    
    /**
     * @var int
     *
     * @ORM\Column()
     */
    private $h7;
    
    /**
     * @var int
     *
     * @ORM\Column()
     */
    private $h8;
    
    /**
     * @var int
     *
     * @ORM\Column()
     */
    private $h9;
    
    /**
     * @var int
     *
     * @ORM\Column()
     */
    private $h10;
    
    /**
     * @var int
     *
     * @ORM\Column()
     */
    private $h11;
    
    /**
     * @var int
     *
     * @ORM\Column()
     */
    private $h12;
    
    /**
     * @var int
     *
     * @ORM\Column()
     */
    private $h13;
    
    /**
     * @var int
     *
     * @ORM\Column()
     */
    private $h14;
    
    /**
     * @var int
     *
     * @ORM\Column()
     */
    private $h15;
    
    /**
     * @var int
     *
     * @ORM\Column()
     */
    private $h16;
    
    /**
     * @var int
     *
     * @ORM\Column()
     */
    private $h17;
    
    /**
     * @var int
     *
     * @ORM\Column()
     */
    private $h18;
    
    /**
     * @var int
     *
     * @ORM\Column()
     */
    private $h19;
    
    /**
     * @var int
     *
     * @ORM\Column()
     */
    private $h20;
    
    /**
     * @var int
     *
     * @ORM\Column()
     */
    private $h21;
    
    /**
     * @var int
     *
     * @ORM\Column()
     */
    private $h22;
    
    /**
     * @var int
     *
     * @ORM\Column()
     */
    private $h23;
    
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date")
     */
    private $date;

    public function __construct()
    {
        $this->h0	= 0;
        $this->h1	= 0;
        $this->h2	= 0;
        $this->h3	= 0;
        $this->h4	= 0;
        $this->h5	= 0;
        $this->h6	= 0;
        $this->h7	= 0;
        $this->h8	= 0;
        $this->h9	= 0;
        $this->h10	= 0;
        $this->h11	= 0;
        $this->h12	= 0;
        $this->h13	= 0;
        $this->h14	= 0;
        $this->h15	= 0;
        $this->h16	= 0;
        $this->h17	= 0;
        $this->h18	= 0;
        $this->h19	= 0;
        $this->h20	= 0;
        $this->h21	= 0;
        $this->h22	= 0;
        $this->h23	= 0;
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
    public function getAction()
    {
    	return $this->action;
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
     * @param string action
     *
     * @return AnalyticsHourlyMoocStats
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }


    /**
     * @param \DateTime $lastName
     *
     * @return AnalyticsHourlyMoocStats
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @param AbstractWorkspace $workspace
     *
     * @return AnalyticsHourlyMoocStats
     */
    public function setWorkspace(AbstractWorkspace $workspace)
    {
        $this->workspace = $workspace;

        return $this;
    }

    public function getH0() { return $this->h0; }
    public function getH1() { return $this->h1; }
    public function getH2() { return $this->h2; }
    public function getH3() { return $this->h3; }
    public function getH4() { return $this->h4; }
    public function getH5() { return $this->h5; }
    public function getH6() { return $this->h6; }
    public function getH7() { return $this->h7; }
    public function getH8() { return $this->h8; }
    public function getH9() { return $this->h9; }
    public function getH10() { return $this->h10; }
    public function getH11() { return $this->h11; }
    public function getH12() { return $this->h12; }
    public function getH13() { return $this->h13; }
    public function getH14() { return $this->h14; }
    public function getH15() { return $this->h15; }
    public function getH16() { return $this->h16; }
    public function getH17() { return $this->h17; }
    public function getH18() { return $this->h18; }
    public function getH19() { return $this->h19; }
    public function getH20() { return $this->h20; }
    public function getH21() { return $this->h21; }
    public function getH22() { return $this->h22; }
    public function getH23() { return $this->h23; }


    public function setH0($val) { $this->h0 = $val; }
    public function setH1($val) { $this->h1 = $val; }
    public function setH2($val) { $this->h2 = $val; }
    public function setH3($val) { $this->h3 = $val; }
    public function setH4($val) { $this->h4 = $val; }
    public function setH5($val) { $this->h5 = $val; }
    public function setH6($val) { $this->h6 = $val; }
    public function setH7($val) { $this->h7 = $val; }
    public function setH8($val) { $this->h8 = $val; }
    public function setH9($val)	{ $this->h9 = $val; }
    public function setH10($val) { $this->h10 = $val; }
    public function setH11($val) { $this->h11 = $val; }
    public function setH12($val) { $this->h12 = $val; }
    public function setH13($val) { $this->h13 = $val; }
    public function setH14($val) { $this->h14 = $val; }
    public function setH15($val) { $this->h15 = $val; }
    public function setH16($val) { $this->h16 = $val; }
    public function setH17($val) { $this->h17 = $val; }
    public function setH18($val) { $this->h18 = $val; }
    public function setH19($val) { $this->h19 = $val; }
    public function setH20($val) { $this->h20 = $val; }
    public function setH21($val) { $this->h21 = $val; }
    public function setH22($val) { $this->h22 = $val; }
    public function setH23($val) { $this->h23 = $val; }
    

    public function setHourValue($hour, $value) {
    	switch ($hour) {
    		case 0:
    			$this->setH0($value);
    			break;
    		case 1:
    			$this->setH1($value);
    			break;
    		case 2:
    			$this->setH2($value);
    			break;
    		case 3:
    			$this->setH3($value);
    			break;
    		case 4:
    			$this->setH4($value);
    			break;
    		case 5:
    			$this->setH5($value);
    			break;
    		case 6:
    			$this->setH6($value);
    			break;
    		case 7:
    			$this->setH7($value);
    			break;
    		case 8:
    			$this->setH8($value);
    			break;
    		case 9:
    			$this->setH9($value);
    			break;
    		case 10:
    			$this->setH10($value);
    			break;
    		case 11:
    			$this->setH11($value);
    			break;
    		case 12:
    			$this->setH12($value);
    			break;
    		case 13:
    			$this->setH13($value);
    			break;
    		case 14:
    			$this->setH14($value);
    			break;
    		case 15:
    			$this->setH15($value);
    			break;
    		case 16:
    			$this->setH16($value);
    			break;
    		case 17:
    			$this->setH17($value);
    			break;
    		case 18:
    			$this->setH18($value);
    			break;
    		case 19:
    			$this->setH19($value);
    			break;
    		case 20:
    			$this->setH20($value);
    			break;
    		case 21:
    			$this->setH21($value);
    			break;
    		case 22:
    			$this->setH22($value);
    			break;
    		case 23:
    			$this->setH23($value);
    			break;
    	}
    }
    
    public function getHourValue($hour) {
    	switch ($hour) {
    		case 0:
    			return $this->getH0();
    			break;
    		case 1:
    			return $this->getH1();
    			break;
    		case 2:
    			return $this->getH2();
    			break;
    		case 3:
    			return $this->getH3();
    			break;
    		case 4:
    			return $this->getH4();
    			break;
    		case 5:
    			return $this->getH5();
    			break;
    		case 6:
    			return $this->getH6();
    			break;
    		case 7:
    			return $this->getH7();
    			break;
    		case 8:
    			return $this->getH8();
    			break;
    		case 9:
    			return $this->getH9();
    			break;
    		case 10:
    			return $this->getH10();
    			break;
    		case 11:
    			return $this->getH11();
    			break;
    		case 12:
    			return $this->getH12();
    			break;
    		case 13:
    			return $this->getH13();
    			break;
    		case 14:
    			return $this->getH14();
    			break;
    		case 15:
    			return $this->getH15();
    			break;
    		case 16:
    			return $this->getH16();
    			break;
    		case 17:
    			return $this->getH17();
    			break;
    		case 18:
    			return $this->getH18();
    			break;
    		case 19:
    			return $this->getH19();
    			break;
    		case 20:
    			return $this->getH20();
    			break;
    		case 21:
    			return $this->getH21();
    			break;
    		case 22:
    			return $this->getH22();
    			break;
    		case 23:
    			return $this->getH23();
    			break;
    	}
    }
    
    public function __toString() {
    	return "AnalyticsHourlyMoocStats(".$this->id.")";
    }
}
