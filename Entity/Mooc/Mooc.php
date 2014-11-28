<?php

namespace Claroline\CoreBundle\Entity\Mooc;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContext;

/**
 * Mooc
 *
 * @ORM\Table(name="claro_mooc")
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Mooc\MoocRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Mooc
{
	const DEFAULT_IMAGE_PATH = "../../default-images/logo-solerni-color.png";

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
     * @ORM\Column(name="about_page_description", type="text", nullable=true)
     */
    private $aboutPageDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="illustration_path", type="string", length=255, nullable=true)
     */
    private $illustrationPath = self::DEFAULT_IMAGE_PATH;
    
     /**
     *
     * @Assert\File(
     *     maxSize = "2048k"
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
     * @var integer
     * @Assert\GreaterThanOrEqual(value="0", message = "Integer must be positive")
     * @ORM\Column(name="duration", type="integer", length=255, nullable=true)
     */
    private $duration;

    /**
     * @var integer
     * @Assert\GreaterThanOrEqual(value="0", message = "Integer must be positive")
     * @ORM\Column(name="weekly_time", type="integer", length=255, nullable=true)
     */
    private $weeklyTime;

    /**
     * @var string
     * 
     * @ORM\Column(name="certification_type", type="json_array")
     */
    private $certificationType = array();
    
    /**
     * @var integer
     * @Assert\GreaterThanOrEqual(value="0", message = "Integer must be positive")
     * @ORM\Column(name="cost", type="integer", nullable=true)
     */
    private $cost = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="language", type="string", length=10, nullable=true)
	 * @Assert\NotBlank(groups={"language_required"})
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
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Mooc\MoocCategory",
     *     inversedBy="moocs"
     * )
     * @ORM\JoinTable(name="claro_moocs_to_categories")
     */
    private $categories;
    
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * 
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Mooc\MoocSession",
     *     mappedBy="mooc",
     *     cascade={"persist", "remove"}
     * )
     */
    private $moocSessions;

    /**
     * @var Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace
     * 
     * @ORM\OneToOne(
     *  targetEntity="Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace", 
     *  inversedBy="mooc"
     * )
     * 
     */
    private $workspace;

    /**
     * @var string
     *
     * @ORM\Column(name="badgesText", type="text", nullable=true)
     */
    private $badgesText;


    /**
     * @var string
     *
     * @ORM\Column(name="badgesUrl", type="text", nullable=true)
     */
    private $badgesUrl;
    

    /**
     * @var string
     *
     * @ORM\Column(name="knowledgeBadgesUrl", type="text", nullable=true)
     */
    private $knowledgeBadgesUrl;
    

    /**
     * @var string
     *
     * @ORM\Column(name="googleAnalyticsToken", type="text", nullable=true)
     */
    private $googleAnalyticsToken;
    
    /**
     * @var Claroline\CoreBundle\Entity\Resource\ResourceNode
     * 
     * @ORM\OneToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode")
     * 
     */
    private $lesson;
    
    /**
     * @var Claroline\CoreBundle\Entity\Resource\ResourceNode
     * 
     * @ORM\OneToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode")
     * 
     */
    private $blog;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $showResourceManager;
    
    /**
     * @var Claroline\CoreBundle\Entity\Mooc\MoocOwner
     * 
     * @ORM\ManyToOne(
     * targetEntity="Claroline\CoreBundle\Entity\Mooc\MoocOwner",
     * inversedBy="moocs",
     * cascade={"persist"}
     * )
     * 
     */
    private $owner;
    
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * 
     * @ORM\ManyToMany(
     *      targetEntity="Claroline\CoreBundle\Entity\Mooc\MoocAccessConstraints",
     *      inversedBy="moocs",
     *      cascade={"persist"}
     * )
     * @ORM\JoinTable(name="claro_mooc_constraints_to_moocs")
     */
    private $accessConstraints;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $showWorkGroup;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $workGroup;
    
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

    public function getIllustrationPath() {
        return $this->illustrationPath;
    }

    public function getIllustrationName() {
        return $this->illustrationName;
    }

    public function setIllustrationPath($illustrationPath) {
        $this->illustrationPath = $illustrationPath;
    }

    public function setIllustrationName($illustrationName) {
        $this->illustrationName = $illustrationName;
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
     * isPublic
     *
     * @return boolean 
     */
    public function isPublic()
    {
        return $this->isPublic;
    }

    /**
     * Set duration
     *
     * @param integer $duration
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
     * @return integer 
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Set weeklyTime
     *
     * @param integer $weeklyTime
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
     * @return integer 
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
    
    /*
     * Check if the is gratis
     * 
     * @return boolean
     */
    public function isGratis() 
    {
        if ($this->getCost() == 0) {
            return true;
        } else {
            return false;
        }
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
    
    /**
     * 
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getMoocSessions()
    {
        return $this->moocSessions;
    }

    /**
     * Get workspace 
     * 
     * @return AbstractWorkspace
     */
    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function setMoocSessions($moocSessions)
    {
        $this->moocSessions = $moocSessions;
        
        return $this;
    }

    public function setWorkspace( $workspace )
    {
        $this->workspace = $workspace;
        
        return $this;
    }
    
    /**
     * @param $file
     *
     * @return Mooc
     */
    public function setFile($file)
    {
         $this->file = $file;

        return $this;
    }

    /**
     * @return $file
     */
    public function getFile()
    {
        return $this->file;
    }
    
    
    public function getCertificationType()
    {
        return $this->certificationType;
    }

    public function setCertificationType($certificationType)
    {
        $this->certificationType = $certificationType;
    }

    public function getAboutPageDescription() {
        return $this->aboutPageDescription;
    }

    public function setAboutPageDescription($aboutPageDescription) {
        $this->aboutPageDescription = $aboutPageDescription;
    }
    
    public function getCategories() {
        return $this->categories;
    }
    public function setCategories($categories) {
        $this->categories = $categories;
    }
    
    public function getLesson() {
        return $this->lesson;
    }

    public function setLesson( $lesson ) {
        $this->lesson = $lesson;
    }
    
    public function getBlog() {
        return $this->blog;
    }

    public function setBlog( $blog ) {
        $this->blog = $blog;
    }
    
    public function getOwner() {
        return $this->owner;
    }
    
    public function setOwner($owner) {
        $this->owner = $owner;
    }
    
    public function getIsPublic() {
        return $this->isPublic;
    }
    
    public function getAccessConstraints() {
        return $this->accessConstraints;
    }

    public function setAccessConstraints($accessContraints) {
        $this->accessConstraints = $accessContraints;
    }

    public function setGratis($gratis) {
        
    }
            
    /* FILE UPLOAD METHODS */
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

        if ( ! realpath($uploadRootDir) ) {
            try {
                mkdir( $uploadRootDir );
            } catch( \Symfony\Component\Filesystem\Exception\IOException $ex ) {
                throw new \Exception(
                    sprintf(
                        "Cannot create '%s' folder %s",
                        $uploadRootDir,
                        $ex->getMessage()
                    )
                );
            }
        }
        
        if (false === realpath($uploadRootDir)) {
            throw new \Exception(
                sprintf(
                    "Invalid upload root dir '%s'for uploading badge images.",
                    $uploadRootDir
                )
            );
        }

        return realpath($uploadRootDir);
    }
    
     /**
     * @return null|string
     */
    public function getIllustrationAbsolutePath()
    {
        return (null === $this->illustrationPath) ? null : $this->getUploadRootDir() . DIRECTORY_SEPARATOR . $this->illustrationPath;
    }

    /**
     * @return null|string
     */
    public function getIllustrationWebPath()
    {
        return (null === $this->illustrationPath) ? null : $this->getUploadDir() . DIRECTORY_SEPARATOR . $this->illustrationPath;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUploadIllustration()
    {
        if (null !== $this->file) {
            // faites ce que vous voulez pour générer un nom unique
            $this->illustrationPath = sha1(uniqid(mt_rand(), true)).'.'.$this->file->guessExtension();
        }
    }
    
    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function uploadIllustration()
    {
        if (null === $this->file) {
            return;
        }
        
        $this->file->move($this->getUploadRootDir(), $this->illustrationPath);

        unset($this->file);
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUploadIllustration()
    {
        if (($file = $this->getIllustrationAbsolutePath()) && $this->getIllustrationPath() != self::DEFAULT_IMAGE_PATH) {
            unlink($file);
        }
    }
    
    
    
    public function getAccessRoleIds() {
        
        $accessRolesIds = array();
        $roleManager = $this->get('claroline.manager.role_manager');
        $accessRolesIds [] = $roleManager->getRoleByName('ROLE_ADMIN')->getId();
        if ($this->isPublic()) {
            $accessRolesIds [] = $roleManager->getRoleByName('ROLE_ANONYMOUS')->getId();
            $accessRolesIds [] = $roleManager->getRoleByName('ROLE_USER')->getId();
        }
        foreach ($this->getAccessConstraints() as $constraint) {
            foreach ($constraint->getRoles() as $role) {
                $accessRolesIds [] = $role->getId();
            }
        }
        return $accessRolesIds;
    }
 
    private function get($serviceName) 
    {
        global $kernel;
        if ('AppCache' == get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }
        return $kernel->getContainer()->get($serviceName);
    }
    
    public function getGoogleAnalyticsToken() {
    	return $this->googleAnalyticsToken;
    }
    
    public function setGoogleAnalyticsToken($gaToken) {
    	$this->googleAnalyticsToken = $gaToken;
    }
    
	public function getBadgesText() {
    	return $this->badgesText;
    }

    public function getBadgesUrl() {
    	return $this->badgesUrl;
    }

    public function getKnowledgeBadgesUrl() {
    	return $this->knowledgeBadgesUrl;
    }

    /**
     * Returns a HREF version of the badges url.
     * @return string
     */
    public function getNiceBadgesUrl() {
    	if (strpos($this->badgesUrl, "http") !== 0) {
    		return "//".$this->badgesUrl;
    	} else {
    		return $this->badgesUrl;
    	}
    }
    
    public function setBadgesText($badgesText) {
    	 $this->badgesText = $badgesText;
    }
    
    public function setBadgesUrl($badgesUrl) {
    	$this->badgesUrl = $badgesUrl;
    }
    
    public function setKnowledgeBadgesUrl($knowledgeBadgesUrl) {
    	$this->knowledgeBadgesUrl = $knowledgeBadgesUrl;
    }
    
    public function isShowResourceManager() {
    	return $this->showResourceManager;
    }
    
    public function setShowResourceManager($showResourceManager) {
    	$this->showResourceManager = $showResourceManager;
    }
    
    public function setWorkGroup($workGroup) {
    	$this->workGroup = $workGroup;
    }
    
    public function getWorkGroup() {
    	return $this->workGroup;
    }
    
    public function setShowWorkGroup($showWorkGroup) {
    	$this->showWorkGroup = $showWorkGroup;
    }
    
    public function isShowWorkGroup() {
    	return $this->showWorkGroup;
    }
}
