<?php

namespace Claroline\CoreBundle\Entity\Mooc;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Claroline\CoreBundle\Entity\AbstractIndexable;
use Symfony\Component\Validator\Constraints as Assert;
use Claroline\CoreBundle\Entity\User;

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
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Claroline\CoreBundle\Entity\Group",
     *     cascade={"persist"},
     *     inversedBy="moocSessions"
     * )
     * @ORM\JoinTable(name="claro_group_mooc_session")
     */
    private $groups;

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
     * @var integer
     *
     * @ORM\Column(name="archived", type="integer", nullable=true)
     * @Assert\GreaterThanOrEqual(value="0", message = "Integer must be positive")
     */
    private $archived;

    /**
     * @return int
     */
    public function getArchived()
    {
        return $this->archived;
    }

    /**
     * @param int $archived
     */
    public function setArchived($archived)
    {
        $this->archived = $archived;
    }


                
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
     * Get all Users (from Groups too)
     *
     * @return PersistentCollection
     */
     public function getAllUsers($filteredRoles = array()) {
    	$managers = array();
    	$workspace = $this->getMooc()->getWorkspace();
    	
    	// Get all users of session
    	$allUsers = array();

    	foreach ($this->getUsers() as $user) {
    		if (!in_array($user, $allUsers)) {
    			$allUsers[] = $user;
    		}
    	}
    	foreach ($this->getGroups() as $group) {
    		foreach ($group->getUsers() as $user) {
    			if (!in_array($user, $allUsers)) {
    				$allUsers[] = $user;
	    		}
    		}
    	}
    	
    	// Filter them
    	foreach ($allUsers as $key => $user) {
    		foreach ($user->getRoles() as $userRole) {
    			if (in_array($userRole, $filteredRoles)) {
    				unset($allUsers[$key]);
    				break;
    			}
    		}
    	}

    	return $allUsers;
    }

    /**
     * Get all Users activate Notification
     *
     * @return Array (mail -> { notifs... } )
     */
    public function getUsersNotif() {


        // Get all users of session who activated notification
        $allUsers = array();

        foreach ($this->getUsers() as $user) {
            if (($user->getNotifarticle()) || ($user->getNotifsujettheme())  || ($user->getNotifciter()) || ($user->getNotiflike())) {
                if (!in_array($user, $allUsers)) {

                    $user = array($user->getLastName(), $user->getFirstName(), $user->getMail(), $user->getNotifarticle() , $user->getNotifsujettheme()  , $user->getNotifciter() , $user->getNotiflike()) ;
                    $allUsers[] = $user;

                }
            }
        }

        return $allUsers;
    }


    /**
     * Get all Users activate Notification
     *
     * @return Array (mail -> { notifs... } )
     */
    public function getUsersNotifTheme() {


        // Get all users of session who activated notification
        $allUsers = array();

        foreach ($this->getUsers() as $user) {

            if ($user->getNotifciter()) {
                if (!in_array($user, $allUsers)) {
                    $allUsers[] = $user;

                }
            }
        }

        return $allUsers;
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
     * Get Groups
     * 
     * @return PersistentCollection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Set Groups
     * 
     * @param  $groups
     * @return MoocSession
     */
    public function setGroups( $groups )
    {
        $this->groups = $groups;
        
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
       
       $doc->start_date             	= $this->getStartDate();
       $doc->end_date               	= $this->getEndDate();
       $doc->start_inscription_date 	= $this->getStartInscriptionDate();
       $doc->end_inscription_date   	= $this->getEndInscriptionDate();
       $doc->max_users_i            	= $this->getMaxUsers();
       $doc->title                  	= $this->getTitle();
       
       $mooc                        	= $this->getMooc();
       $doc->mooc_id                	= $mooc->getId();
       $doc->mooc_illustration_path 	= $mooc->getIllustrationWebPath();
       $doc->mooc_title_t         		= $mooc->getTitle();
       $doc->mooc_alias             	= $mooc->getAlias();
       $doc->mooc_description_t			= $mooc->getDescription();
       $doc->mooc_about_description_t	= $mooc->getAboutPageDescription();
       $doc->content_t					= strip_tags( $mooc->getDescription() )
                                            .'<br>'
               								.$mooc->getTitle()
                                            .'<br>'
               								.strip_tags( $mooc->getAboutPageDescription() )
                                            .'<br>'
                                            .$mooc->getOwner()->getName()
                                            ;
       
       $doc->mooc_is_public_b       	= $mooc->isPublic();
       $doc->mooc_duration_i        	= $mooc->getDuration();
       $doc->mooc_weekly_time_i     	= $mooc->getWeeklyTime();
       $doc->mooc_cost_i            	= $mooc->getCost();
       $doc->mooc_language          	= $mooc->getLanguage();
       $doc->mooc_has_video_b       	= $mooc->getHasVideo();
       $doc->mooc_has_subtitle_b    	= $mooc->getHasSubtitle();
       $doc->mooc_view_url          	= $this->get('router')->generate('mooc_view', array(
                                        'moocId'    => $mooc->getId(),
                                        'moocName'  => $mooc->getAlias()
                                      ));
       $doc->mooc_session_learn_url 	= $this->get('router')->generate('mooc_view_session', array(
	                                        'word'      => 'apprendre',
	                                        'sessionId' => $this->getId(),
	                                        'moocId'    => $mooc->getId(),
	                                        'moocName'  => $mooc->getAlias()
	                                      ));
      
       $doc->mooc_category_ids      	= array_map(function($obj) { return $obj->getId(); }, $mooc->getCategories()->toArray());
       
       if ($mooc->getOwner()) {
          $doc->mooc_owner_id       	= $mooc->getOwner()->getId();
          $doc->mooc_owner_name     	= $mooc->getOwner()->getName();
          $doc->mooc_owner_logo_url 	= $mooc->getOwner()->getLogoWebPath();
          $doc->mooc_owner_cat_url  	= $this->get('router')->generate('solerni_owner_catalogue', array(
                                        'ownerName' => $mooc->getOwner()->getName(),
                                        'ownerId'   => $mooc->getOwner()->getId()
                                      ));
       }
       $doc->wks_id                 	= $mooc->getWorkspace()->getId();
       
       return $doc;
    }
    
    public function getAccessRoleIds()
    {
        return $this->getMooc()->getAccessRoleIds();
    }
}
