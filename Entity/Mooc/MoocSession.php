<?php

namespace Claroline\CoreBundle\Entity\Mooc;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Claroline\CoreBundle\Entity\AbstractIndexable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * MoocSession
 *
 * @ORM\Table(name="claro_mooc_session")
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Mooc\MoocSessionRepository")
 */
class MoocSession extends AbstractIndexable
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
     * @ORM\Column(name="start_date", type="datetime", nullable=true)
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_date", type="datetime", nullable=true)
     */
    private $endDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_inscription_date", type="datetime", nullable=true)
     */
    private $startInscriptionDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_inscription_date", type="datetime", nullable=true)
     */
    private $endInscriptionDate;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @var ArrayCollection
     * 
     * @ORM\ManyToMany(targetEntity="Claroline\CoreBundle\Entity\User",
     *     cascade={"persist"},
     *     inversedBy="moocSessions"
     * )
     * @ORM\JoinTable(name="claro_user_mooc_session")
     */
    private $users;

    /**
     * @var integer
     *
     * @ORM\Column(name="max_users", type="integer", nullable=true)
     * @Assert\GreaterThanOrEqual(value="0", message = "Integer must be positive")
     */
    private $maxUsers;

     /**
     * @ORM\ManyToOne(
     *      targetEntity="Claroline\CoreBundle\Entity\Mooc\Mooc",
     *      inversedBy="moocSessions"
     * )
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    private $mooc;
    
    /**
     * @var Claroline\CoreBundle\Entity\Resource\ResourceNode
     * 
     * @ORM\OneToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode")
     * 
     */
    private $forum;


    /**
     * @var SessionsByUsers[]|ArrayCollection
     *
     * @ORM\OneToMany(
     *      targetEntity="Claroline\CoreBundle\Entity\Mooc\SessionsByUsers",
     *      mappedBy="moocSession",
     *      cascade={"all"}
     * )
     */
    protected $sessionsByUsers;

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
     * @return PersistentCollection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Set Users
     * 
     * @param  $users
     * @return MoocSession
     */
    public function setUsers( $users )
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
     * @return Mooc
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
    
    public function getForum() {
        return $this->forum;
    }

    public function setForum( $forum ) {
        $this->forum = $forum;
    }

    

    public function getSessionsByUsers(){
        return $this->sessionsByUsers;
    }

    public function setSessionsByUsers(ArrayCollection $sessionsByUsers){
        $this->sessionsByUsers = $sessionsByUsers;
    }

    public function fillIndexableDocument(&$doc)
    {
       $doc = parent::fillIndexableDocument($doc);
       
       $doc->start_date             = $this->getStartDate();
       $doc->end_date               = $this->getEndDate();
       $doc->start_inscription_date = $this->getStartInscriptionDate();
       $doc->end_inscription_date   = $this->getEndInscriptionDate();
       $doc->max_users_i            = $this->getMaxUsers();
       $doc->title                  = $this->getTitle();
       
       $mooc                        = $this->getMooc();
       $doc->mooc_id                = $mooc->getId();
       $doc->mooc_illustration_path = $mooc->getIllustrationWebPath();
       $doc->mooc_title             = $mooc->getTitle();
       $doc->mooc_alias             = $mooc->getAlias();
       $doc->content                = $mooc->getDescription();
       $doc->mooc_is_public_b       = $mooc->isPublic();
       $doc->mooc_duration_i        = $mooc->getDuration();
       $doc->mooc_weekly_time_i     = $mooc->getWeeklyTime();
       $doc->mooc_cost_i            = $mooc->getCost();
       $doc->mooc_language          = $mooc->getLanguage();
       $doc->mooc_has_video_b       = $mooc->getHasVideo();
       $doc->mooc_has_subtitle_b    = $mooc->getHasSubtitle();
       $doc->mooc_view_url          = $this->get('router')->generate('mooc_view', array(
                                        'moocId'    => $mooc->getId(),
                                        'moocName'  => $mooc->getAlias()
                                      ));
       $doc->mooc_session_learn_url = $this->get('router')->generate('mooc_view_session', array(
                                        'word'      => 'apprendre',
                                        'sessionId' => $this->getId(),
                                        'moocId'    => $mooc->getId(),
                                        'moocName'  => $mooc->getAlias()
                                      ));
      
       $doc->mooc_category_ids      = array_map(function($obj) { return $obj->getId(); }, $mooc->getCategories()->toArray());
       
       if ($mooc->getOwner()) {
            $doc->mooc_owner_id     = $mooc->getOwner()->getId();
            $doc->mooc_owner_name   = $mooc->getOwner()->getName();
       }
       $doc->wks_id                 = $mooc->getWorkspace()->getId();
       
       return $doc;
    }
    
    public function getAccessRoleIds()
    {
        return $this->getMooc()->getAccessRoleIds();
    }
}
