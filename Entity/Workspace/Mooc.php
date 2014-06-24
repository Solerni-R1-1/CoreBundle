<?php

namespace Claroline\CoreBundle\Entity\Workspace;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Mooc
 *
 * @ORM\Table(name="claro_mooc")
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\MoocRepository")
 */
class Mooc
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
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="alias", type="string", length=255, nullable=true)
     */
    private $alias;


    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="desc_img", type="string", length=255, nullable=true)
     */
    private $descImg;
    
     /**
     * @var UploadedFile
     *
     * @Assert\Image(
     *     maxSize = "2048k",
     *     minWidth = 64,
     *     minHeight = 64
     * )
     */
    protected $file;

    /**
     * @var integer
     *
     * @ORM\Column(name="post_end_action", type="integer", nullable=true)
     */
    private $postEndAction;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_public", type="boolean", nullable=true)
     */
    private $isPublic;

    /**
     * @var string
     *
     * @ORM\Column(name="duration", type="string", length=255, nullable=true)
     */
    private $duration;

    /**
     * @var string
     *
     * @ORM\Column(name="weekly_time", type="string", length=255, nullable=true)
     */
    private $weeklyTime;

    /**
     * @var integer
     *
     * @ORM\Column(name="cost", type="integer", nullable=true)
     */
    private $cost;

    /**
     * @var string
     *
     * @ORM\Column(name="language", type="string", length=10, nullable=true)
     */
    private $language;

    /**
     * @var boolean
     *
     * @ORM\Column(name="has_video", type="boolean", nullable=true)
     */
    private $hasVideo;

    /**
     * @var boolean
     *
     * @ORM\Column(name="has_subtitle", type="boolean", nullable=true)
     */
    private $hasSubtitle;

    /**
     * @var string
     *
     * @ORM\Column(name="prerequisites", type="text", nullable=true)
     */
    private $prerequisites;

    /**
     * @var string
     *
     * @ORM\Column(name="team_description", type="text", nullable=true)
     */
    private $teamDescription;

    /**
     * @var boolean
     *
     * @ORM\Column(name="has_facebook_share", type="boolean", nullable=true)
     */
    private $hasFacebookShare;

    /**
     * @var boolean
     *
     * @ORM\Column(name="has_tweeter_share", type="boolean", nullable=true)
     */
    private $hasTweeterShare;

    /**
     * @var boolean
     *
     * @ORM\Column(name="has_gplus_share", type="boolean", nullable=true)
     */
    private $hasGplusShare;

    /**
     * @var boolean
     *
     * @ORM\Column(name="has_linkin_share", type="boolean", nullable=true)
     */
    private $hasLinkedinShare;
    
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * 
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\MoocSession",
     *     mappedBy="mooc"
     * )
     */
    private $moocSessions;

    
    /**
     * @var Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace
     * 
     * @ORM\OneToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace", inversedBy="mooc")
     * 
     */
    private $workspace;
    
    
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
     * Set title
     *
     * @param string $title
     * @return Mooc
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
     * Set alias
     *
     * @param string $alias
     * @return Mooc
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * Get alias
     *
     * @return string 
     */
    public function getAlias()
    {
        return $this->alias;
    }

    
    /**
     * Set description
     *
     * @param string $description
     * @return Mooc
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set descImg
     *
     * @param string $descImg
     * @return Mooc
     */
    public function setDescImg($descImg)
    {
        $this->descImg = $descImg;

        return $this;
    }

    /**
     * Get descImg
     *
     * @return string 
     */
    public function getDescImg()
    {
        return $this->descImg;
    }

    /**
     * Set postEndAction
     *
     * @param integer $postEndAction
     * @return Mooc
     */
    public function setPostEndAction($postEndAction)
    {
        $this->postEndAction = $postEndAction;

        return $this;
    }

    /**
     * Get postEndAction
     *
     * @return integer 
     */
    public function getPostEndAction()
    {
        return $this->postEndAction;
    }

    /**
     * Set isPublic
     *
     * @param boolean $isPublic
     * @return Mooc
     */
    public function setIsPublic($isPublic)
    {
        $this->isPublic = $isPublic;

        return $this;
    }

    /**
     * Get isPublic
     *
     * @return boolean 
     */
    public function getIsPublic()
    {
        return $this->isPublic;
    }

    /**
     * Set duration
     *
     * @param string $duration
     * @return Mooc
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * Get duration
     *
     * @return string 
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Set weeklyTime
     *
     * @param string $weeklyTime
     * @return Mooc
     */
    public function setWeeklyTime($weeklyTime)
    {
        $this->weeklyTime = $weeklyTime;

        return $this;
    }

    /**
     * Get weeklyTime
     *
     * @return string 
     */
    public function getWeeklyTime()
    {
        return $this->weeklyTime;
    }

    /**
     * Set cost
     *
     * @param integer $cost
     * @return Mooc
     */
    public function setCost($cost)
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * Get cost
     *
     * @return integer 
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * Set language
     *
     * @param string $language
     * @return Mooc
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language
     *
     * @return string 
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set hasVideo
     *
     * @param boolean $hasVideo
     * @return Mooc
     */
    public function setHasVideo($hasVideo)
    {
        $this->hasVideo = $hasVideo;

        return $this;
    }

    /**
     * Get hasVideo
     *
     * @return boolean 
     */
    public function getHasVideo()
    {
        return $this->hasVideo;
    }

    /**
     * Set hasSubtitle
     *
     * @param boolean $hasSubtitle
     * @return Mooc
     */
    public function setHasSubtitle($hasSubtitle)
    {
        $this->hasSubtitle = $hasSubtitle;

        return $this;
    }

    /**
     * Get hasSubtitle
     *
     * @return boolean 
     */
    public function getHasSubtitle()
    {
        return $this->hasSubtitle;
    }

    /**
     * Set prerequisites
     *
     * @param string $prerequisites
     * @return Mooc
     */
    public function setPrerequisites($prerequisites)
    {
        $this->prerequisites = $prerequisites;

        return $this;
    }

    /**
     * Get prerequisites
     *
     * @return string 
     */
    public function getPrerequisites()
    {
        return $this->prerequisites;
    }

    /**
     * Set teamDescription
     *
     * @param string $teamDescription
     * @return Mooc
     */
    public function setTeamDescription($teamDescription)
    {
        $this->teamDescription = $teamDescription;

        return $this;
    }

    /**
     * Get teamDescription
     *
     * @return string 
     */
    public function getTeamDescription()
    {
        return $this->teamDescription;
    }

    /**
     * Set hasFacebookShare
     *
     * @param boolean $hasFacebookShare
     * @return Mooc
     */
    public function setHasFacebookShare($hasFacebookShare)
    {
        $this->hasFacebookShare = $hasFacebookShare;

        return $this;
    }

    /**
     * Get hasFacebookShare
     *
     * @return boolean 
     */
    public function getHasFacebookShare()
    {
        return $this->hasFacebookShare;
    }

    /**
     * Set hasTweeterShare
     *
     * @param boolean $hasTweeterShare
     * @return Mooc
     */
    public function setHasTweeterShare($hasTweeterShare)
    {
        $this->hasTweeterShare = $hasTweeterShare;

        return $this;
    }

    /**
     * Get hasTweeterShare
     *
     * @return boolean 
     */
    public function getHasTweeterShare()
    {
        return $this->hasTweeterShare;
    }

    /**
     * Set hasGplusShare
     *
     * @param boolean $hasGplusShare
     * @return Mooc
     */
    public function setHasGplusShare($hasGplusShare)
    {
        $this->hasGplusShare = $hasGplusShare;

        return $this;
    }

    /**
     * Get hasGplusShare
     *
     * @return boolean 
     */
    public function getHasGplusShare()
    {
        return $this->hasGplusShare;
    }

    /**
     * Set hasLinkedinShare
     *
     * @param boolean $hasLinkedinShare
     * @return Mooc
     */
    public function setHasLinkedinShare($hasLinkedinShare)
    {
        $this->hasLinkedinShare = $hasLinkedinShare;

        return $this;
    }

    /**
     * Get hasLinkedinShare
     *
     * @return boolean 
     */
    public function getHasLinkedinShare()
    {
        return $this->hasLinkedinShare;
    }
    
    public function getMoocSessions()
    {
        return $this->moocSessions;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function setMoocSessions(\Doctrine\Common\Collections\ArrayCollection $moocSessions)
    {
        $this->moocSessions = $moocSessions;
        
        return $this;
    }

    public function setWorkspace(\Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace $workspace)
    {
        $this->workspace = $workspace;
        
        return $this;
    }
    
    /**
     * @param UploadedFile $file
     *
     * @return Mooc
     */
    public function setFile(UploadedFile $file)
    {
         $this->file = $file;

        return $this;
    }

    /**
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }
    
    /**
     * @return string
     */
    protected function getUploadDir()
    {
        return sprintf("uploads%smooc", DIRECTORY_SEPARATOR);
    }

     /**
     * @throws \Exception
     * @return string
     */
    protected function getUploadRootDir()
    {
        $ds = DIRECTORY_SEPARATOR;

        $uploadRootDir = sprintf(
            '%s%s..%s..%s..%s..%s..%s..%s..%sweb%s%s',
            __DIR__, $ds, $ds, $ds, $ds, $ds, $ds, $ds, $ds, $ds, $this->getUploadDir()
        );
        $realpathUploadRootDir = realpath($uploadRootDir);

        if (false === $realpathUploadRootDir) {
            throw new \Exception(
                sprintf(
                    "Invalid upload root dir '%s'for uploading badge images.",
                    $uploadRootDir
                )
            );
        }

        return $realpathUploadRootDir;
    }
    
     /**
     * @return null|string
     */
    public function getAbsolutePath()
    {
        return (null === $this->descImg) ? null : $this->getUploadRootDir() . DIRECTORY_SEPARATOR . $this->descImg;
    }

    /**
     * @return null|string
     */
    public function getWebPath()
    {
        return (null === $this->descImg) ? null : $this->getUploadDir() . DIRECTORY_SEPARATOR . $this->descImg;
    }
}
