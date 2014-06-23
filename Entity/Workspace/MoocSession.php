<?php

namespace Claroline\CoreBundle\Entity\Workspace;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * MoocSession
 *
 * @ORM\Table(name="claro_mooc_session")
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\MoocSessionRepository")
 */
class MoocSession
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
     * @var \DateTime
     *
     * @ORM\Column(name="start_date", type="datetime")
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_date", type="datetime")
     */
    private $endDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_inscription_date", type="datetime")
     */
    private $startInscriptionDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_inscription_date", type="datetime")
     */
    private $endInscriptionDate;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var ArrayCollection
     * 
     * @ORM\ManyToMany(targetEntity="Claroline\CoreBundle\Entity\User",
     *     cascade={"persist"},
     *     mappedBy="moocSessions"
     * )
     */
    private $users;

    /**
     * @var integer
     *
     * @ORM\Column(name="max_users", type="integer")
     */
    private $maxUsers;

    
     /**
     * @ORM\ManyToOne(
     *      targetEntity="Claroline\CoreBundle\Entity\Workspace\Mooc",
     *      inversedBy="moocSessions"
     * )
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    private $mooc;

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
     * Set startDate
     *
     * @param \DateTime $startDate
     * @return MoocSession
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime 
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     * @return MoocSession
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime 
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set startInscriptionDate
     *
     * @param \DateTime $startInscriptionDate
     * @return MoocSession
     */
    public function setStartInscriptionDate($startInscriptionDate)
    {
        $this->startInscriptionDate = $startInscriptionDate;

        return $this;
    }

    /**
     * Get startInscriptionDate
     *
     * @return \DateTime 
     */
    public function getStartInscriptionDate()
    {
        return $this->startInscriptionDate;
    }

    /**
     * Set endInscriptionDate
     *
     * @param \DateTime $endInscriptionDate
     * @return MoocSession
     */
    public function setEndInscriptionDate($endInscriptionDate)
    {
        $this->endInscriptionDate = $endInscriptionDate;

        return $this;
    }

    /**
     * Get endInscriptionDate
     *
     * @return \DateTime 
     */
    public function getEndInscriptionDate()
    {
        return $this->endInscriptionDate;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return MoocSession
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }
    
    /**
     * Get Users
     * 
     * @return ArrayCollection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Set Users
     * 
     * @param ArrayCollection $users
     * @return MoocSession
     */
    public function setUsers(ArrayCollection $users)
    {
        $this->users = $users;
        
        return $this;
    }

    

    /**
     * Set maxUsers
     *
     * @param integer $maxUsers
     * @return MoocSession
     */
    public function setMaxUsers($maxUsers)
    {
        $this->maxUsers = $maxUsers;

        return $this;
    }

    /**
     * Get maxUsers
     *
     * @return integer 
     */
    public function getMaxUsers()
    {
        return $this->maxUsers;
    }
    
    /**
     * 
     * @return mooc
     */
    public function getMooc()
    {
        return $this->mooc;
    }
    
    /**
     * 
     * @return MoocSession
     */
    public function setMooc($mooc)
    {
        $this->mooc = $mooc;
        
        return $this;
    }

    
}
