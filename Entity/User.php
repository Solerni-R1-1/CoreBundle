<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity;

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
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Claroline\CoreBundle\Entity\Mooc\MoocSession;
use UJM\ExoBundle\Entity\Exercise;
use Claroline\CoreBundle\Entity\Mooc\UserMoocPreferences;

/**
 * @ORM\Table(name="claro_user")
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks
 * @DoctrineAssert\UniqueEntity("username")
 * @DoctrineAssert\UniqueEntity("mail")
 * @Assert\Callback(methods={"isPublicUrlValid"})
 */
class User extends AbstractRoleSubject implements Serializable, AdvancedUserInterface, EquatableInterface, OrderableInterface
{
    public static $patternUrlPublic = '#^[0-9a-zA-Z\.\_]*$#';
    public static $patternReplaceUrlPublic = '#[^0-9a-zA-Z\.\_]#';

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
     * @ORM\Column(name="first_name", length=50)
     * @Assert\NotBlank()
     */
    protected $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", length=50)
     * @Assert\NotBlank()
     */
    protected $lastName;

    /**
     * @var string
     *
     * @ORM\Column(unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(min="3")
     * @Assert\Regex(
     *     pattern="/^[\w\.]*$/",
     *     message="special_char_not_allowed"
     * )
     */
    protected $username;

    /**
     * @var string
     *
     * @ORM\Column()
     */
    protected $password;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $locale;

    /**
     * @var string
     *
     * @ORM\Column()
     */
    protected $salt;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"registration"})
     * @Assert\Length(min="4", groups={"registration"})
     */
    protected $plainPassword;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $phone;

    /**
     * @var string
     *
     * @ORM\Column(unique=true)
     * @Assert\NotBlank()
     * @Assert\Email(checkMX = false)
     */
    protected $mail;

    /**
     * @var string
     *
     * @ORM\Column(name="administrative_code", nullable=true)
     */
    protected $administrativeCode;

    /**
     * @var Group[]|ArrayCollection
     *
     * @ORM\ManyToMany(
     *      targetEntity="Claroline\CoreBundle\Entity\Group",
     *      inversedBy="users"
     * )
     * @ORM\JoinTable(name="claro_user_group")
     */
    protected $groups;

    /**
     * @var Role[]|ArrayCollection
     *
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Role",
     *     inversedBy="users",
     *     fetch="EXTRA_LAZY"
     * )
     * @ORM\JoinTable(name="claro_user_role")
     */
    protected $roles;

    /**
     * @var AbstractResource[]|ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode",
     *     mappedBy="creator"
     * )
     */
    protected $resourceNodes;

    /**
     * @var Workspace\AbstractWorkspace
     *
     * @ORM\OneToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace"
     * )
     * @ORM\JoinColumn(name="workspace_id", onDelete="SET NULL")
     */
    protected $personalWorkspace;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creation_date", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    protected $created;

    /**
     * @var UserMessage[]|ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\UserMessage",
     *     mappedBy="user"
     * )
     */
    protected $userMessages;

    /**
     * @var DesktopTool[]|ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Tool\OrderedTool",
     *     mappedBy="user"
     * )
     */
    protected $orderedTools;

    /**
     * @ORM\Column(name="reset_password", nullable=true)
     */
    protected $resetPasswordHash;

    /**
     * @ORM\Column(name="hash_time", type="integer", nullable=true)
     */
    protected $hashTime;

    /**
     * @var UserBadge[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Claroline\CoreBundle\Entity\Badge\UserBadge", mappedBy="user", cascade={"all"})
     */
    protected $userBadges;

    /**
     * @var UserBadge[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Claroline\CoreBundle\Entity\Badge\UserBadge", mappedBy="issuer", cascade={"all"})
     */
    protected $issuedBadges;

    /**
     * @var BadgeClaim[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Claroline\CoreBundle\Entity\Badge\BadgeClaim", mappedBy="user", cascade={"all"})
     * @ORM\JoinColumn(name="badge_claim_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $badgeClaims;

    /**
     * @ORM\Column(nullable=true)
     */
    protected $picture;

    /**
     * @Assert\File(maxSize="6M", maxSizeMessage="maxSizeMessage")
     */
    protected $pictureFile;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $hasAcceptedTerms;

    /**
     * @ORM\Column(name="is_enabled", type="boolean")
     */
    protected $isEnabled = true;

    /**
     * @ORM\Column(name="is_mail_notified", type="boolean")
     */
    protected $isMailNotified = false;

    /**
     * @ORM\Column(name="last_uri", length=255, nullable=true)
     */
    protected $lastUri;

    /**
     * @ORM\Column(name="has_accepted_com_terms", type="boolean", nullable=true)
     */
    protected $hasAcceptedComTerms = false;

    /**
     * @var string
     *
     * @ORM\Column(name="public_url", type="string", nullable=true, unique=true)
     */
    protected $publicUrl;

    /**
     * @ORM\Column(name="has_tuned_public_url", type="boolean")
     */
    protected $hasTunedPublicUrl = false;

    /**
     * @var UserPublicProfilePreferences
     *
     * @ORM\OneToOne(targetEntity="UserPublicProfilePreferences", mappedBy="user", cascade={"all"})
     */
    protected $publicProfilePreferences;

    /**
     * @ORM\Column(name="is_first_visit", type="boolean")
     */
    protected $isFirstVisit = true;

     /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(
     *      targetEntity="Claroline\CoreBundle\Entity\Mooc\MoocSession",
     *      mappedBy="users"
     * )
     */
    protected $moocSessions;


    /**
     * @ORM\Column(name="is_validate", type="boolean")
     */
    protected $isValidate = false;

    /**
     * @var string
     *
     * @ORM\Column(name="key_validate", type="string", nullable=true, unique=false)
     */
    protected $keyValidate;


    /**
     * @var SessionsByUsers[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Claroline\CoreBundle\Entity\Mooc\SessionsByUsers",
     *      mappedBy="user",
     *      cascade={"all"}
     * )
     */
    protected $sessionsByUsers;


    /**
     * @ORM\Column(name="is_facebook_account", type="boolean", nullable=true)
     */
    protected $isFacebookAccount = false;

    /**
     *
     * @ORM\ManyToMany(
     *      targetEntity="Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace",
     *      mappedBy="notifyUsers"
     * )
     * @var Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace
     */
    protected $notifyWorkspaces;

    /**
     * @ORM\OneToMany(
     * 		targetEntity="UJM\ExoBundle\Entity\ExerciseUser",
     * 		mappedBy="user")
     * @var array
     */
    protected $givenUpExercises;


    const GENDER_UNKNOWN = 0;
    const GENDER_MALE = 1;
    const GENDER_FEMALE = 2;
    /**
     * @ORM\Column(type="integer")
     */
    protected $gender = self::GENDER_UNKNOWN;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $country;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $city;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    protected $birthdate;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $website;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $facebook;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $twitter;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $linkedIn;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $googlePlus;

        /**
     * @var $userMoocPreferences[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Claroline\CoreBundle\Entity\Mooc\UserMoocPreferences",
     *      mappedBy="user",
     *      cascade={"all"}
     * )
     */
    protected $userMoocPreferences;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $lockedLogin = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $lockedPassword = 0;


    const FORUM_ORDER_DESC= 0;
    const FORUM_ORDER_ASC = 1;
    const FORUM_ORDER_POP = 2;
    /**
     * @ORM\Column(type="integer")
     */
    protected $forumOrder = self::FORUM_ORDER_ASC;

    public function __construct()
    {
        parent::__construct();
        $this->userMessages      = new ArrayCollection();
        $this->roles             = new ArrayCollection();
        $this->groups            = new ArrayCollection();
        $this->abstractResources = new ArrayCollection();
        $this->salt              = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $this->orderedTools      = new ArrayCollection();
        $this->userBadges        = new ArrayCollection();
        $this->issuedBadges      = new ArrayCollection();
        $this->badgeClaims       = new ArrayCollection();
        $this->moocSessions      = new ArrayCollection();
        $this->sessionsByUsers   = new ArrayCollection();
        $this->keyValidate       = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $this->userMoocPreferences = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    public function setId($id)
    {
    	$this->id = $id;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * @param string $firstName
     *
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @param string $lastName
     *
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        if (null === $password) {
            return;
        }

        $this->password = $password;

        return $this;
    }

    /**
     * @param string $locale
     *
     * @return User
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @param string $plainPassword
     *
     * @return User
     */
    public function setPlainPassword($plainPassword)
    {
        // Check password complexity
        if ( ! $this->checkSolerniPassword($plainPassword) ) {
            return;
        }

        $this->plainPassword = $plainPassword;
        $this->password = null;

        return $this;
    }

    /**
     * @return Group[]|ArrayCollection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Returns the user's roles (including role's ancestors) as an array
     * of string values (needed for Symfony security checks). The roles
     * owned by groups which the user belong can also be included.
     *
     * @param boolean $areGroupsIncluded
     *
     * @return array[string]
     */
    public function getRoles($areGroupsIncluded = true)
    {
        $roleNames = parent::getRoles();

        if ($areGroupsIncluded) {
            foreach ($this->getGroups() as $group) {
                $roleNames = array_unique(array_merge($roleNames, $group->getRoles()));
            }
        }

        return $roleNames;
    }

    public function eraseCredentials()
    {
        $this->plainPassword = null;

        return $this;
    }

    /**
     * @param UserInterface $user
     *
     * @return bool
     */
    public function isEqualTo(UserInterface $user)
    {
        if ($user->getRoles() !== $this->getRoles()) {
            return false;
        }

        if ($this->username !== $user->getUsername()) {
            return false;
        }

        if ($this->id !== $user->getId()) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param  string $phone
     * @return User
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return string
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * @param string $mail
     *
     * @return User
     */
    public function setMail($mail)
    {
        $this->mail = $mail;

        return $this;
    }

    /**
     * @return string
     */
    public function getAdministrativeCode()
    {
        return $this->administrativeCode;
    }

    /**
     * @param string $administrativeCode
     *
     * @return User
     */
    public function setAdministrativeCode($administrativeCode)
    {
        $this->administrativeCode = $administrativeCode;

        return $this;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize(
            array(
                'id' => $this->id,
                'username' => $this->username,
                'roles' => $this->getRoles()
            )
        );
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $unserialized = unserialize($serialized);
        $this->id = $unserialized['id'];
        $this->username = $unserialized['username'];
        $this->rolesStringAsArray = $unserialized['roles'];
        $this->groups = new ArrayCollection();
    }

    /**
     * @param Workspace\AbstractWorkspace $workspace
     *
     * @return User
     */
    public function setPersonalWorkspace($workspace)
    {
        $this->personalWorkspace = $workspace;

        return $this;
    }

    /**
     * @return Workspace\AbstractWorkspace
     */
    public function getPersonalWorkspace()
    {
        return $this->personalWorkspace;
    }

    /**
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->created;
    }

    /**
     * Sets the user creation date.
     *
     * NOTE : creation date is already handled by the timestamp listener; this
     *        setter exists mainly for testing purposes.
     *
     * @param \DateTime $date
     */
    public function setCreationDate(\DateTime $date)
    {
        $this->created = $date;
    }

    /**
     * @return mixed
     */
    public function getPlatformRole()
    {
        $roles = $this->getEntityRoles();

        foreach ($roles as $role) {
            if ($role->getType() != Role::WS_ROLE) {
                return $role;
            }
        }
    }

    /**
     * Replace the old platform role of a user by a new one.
     * @todo This function is working for now but it's buggy. A user can have many platform
     * roles
     *
     * @param Role $platformRole
     */
    public function setPlatformRole($platformRole)
    {
        $roles = $this->getEntityRoles();

        foreach ($roles as $role) {
            if ($role->getType() != Role::WS_ROLE) {
                $removedRole = $role;
            }
        }

        if (isset($removedRole)) {
            $this->roles->removeElement($removedRole);
        }

        $this->roles->add($platformRole);
    }

    /**
     * Replace the old platform roles of a user by a new array.
     *
     * @param $platformRoles
     */
    public function setPlatformRoles($platformRoles)
    {
        $roles = $this->getEntityRoles();
        $removedRoles = array();

        foreach ($roles as $role) {
            if ($role->getType() != Role::WS_ROLE) {
                $removedRoles[] = $role;
            }
        }

        foreach ($removedRoles as $removedRole) {
            $this->roles->removeElement($removedRole);
        }

        foreach ($platformRoles as $platformRole) {
            $this->roles->add($platformRole);
        }
    }

    public function getOrderedTools()
    {
        return $this->orderedTools;
    }

    public function getResetPasswordHash()
    {
        return $this->resetPasswordHash;
    }

    public function setResetPasswordHash($resetPasswordHash)
    {
        $this->resetPasswordHash = $resetPasswordHash;
    }

    public function getHashTime()
    {
        return $this->hashTime;
    }

    public function setHashTime($hashTime)
    {
        $this->hashTime = $hashTime;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Badge\Badge[]|\Doctrine\Common\Collections\ArrayCollection $badges
     *
     * @return User
     */
    public function setUserBadges($badges)
    {
        $this->userBadges = $badges;

        return $this;
    }

    /**
     * @return \Claroline\CoreBundle\Entity\Badge\UserBadge[]|\Doctrine\Common\Collections\ArrayCollection
     */
    public function getUserBadges()
    {
        return $this->userBadges;
    }

    /**
     * @return \Claroline\CoreBundle\Entity\Badge\Badge[]|\Doctrine\Common\Collections\ArrayCollection
     */
    public function getBadges()
    {
        $badges = new ArrayCollection();

        foreach ($this->getUserBadges() as $userBadge) {
            $badges[] = $userBadge->getBadge();
        }

        return $badges;
    }

    /**
     * @param Badge $badge
     *
     * @return bool
     */
    public function hasBadge(badge $badge)
    {
        foreach ($this->getBadges() as $userBadge) {
            if ($userBadge->getId() === $badge->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\BadgeClaim[]|\Doctrine\Common\Collections\ArrayCollection $badgeClaims
     *
     * @return User
     */
    public function setBadgeClaims($badgeClaims)
    {
        $this->badgeClaims = $badgeClaims;
    }

    /**
     * @return \Claroline\CoreBundle\Entity\Badge\BadgeClaim[]|\Doctrine\Common\Collections\ArrayCollection
     */
    public function getBadgeClaims()
    {
        return $this->badgeClaims;
    }

    /**
     * @param Badge $badge
     *
     * @return bool
     */
    public function hasClaimedFor(Badge $badge)
    {
        foreach ($this->getBadgeClaims() as $claimedBadge) {
            if ($badge->getId() === $claimedBadge->getBadge()->getId()) {
                return true;
            }
        }

        return false;
    }

    public function getPictureFile()
    {
        return $this->pictureFile;
    }

    public function setPictureFile(UploadedFile $pictureFile)
    {
        $this->pictureFile = $pictureFile;
    }

    public function setPicture($picture)
    {
        $this->picture = $picture;
    }

    public function getPicture()
    {
        return $this->picture;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function hasAcceptedTerms()
    {
        return $this->hasAcceptedTerms;
    }

    public function setAcceptedTerms($boolean)
    {
        $this->hasAcceptedTerms = $boolean;
    }

    public function getOrderableFields()
    {
        return array('id', 'username', 'lastName', 'firstName', 'mail', 'isValidate');
    }

    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return true;
    }

    public function isCredentialsNonExpired()
    {
        return true;
    }

    public function isEnabled()
    {
        return $this->isEnabled;
    }

    public function setIsEnabled($isEnabled)
    {
        $this->isEnabled = $isEnabled;
    }

    public function setIsMailNotified($isMailNotified)
    {
        $this->isMailNotified = $isMailNotified;
    }

    public function setLockedLogin($lockedLogin)
    {
        $this->lockedLogin = $lockedLogin;
    }

    public function isLockedLogin()
    {
        return $this->lockedLogin;
    }

    public function setLockedPassword($lockedPassword)
    {
        $this->lockedPassword = $lockedPassword;
    }

    public function isLockedPassword()
    {
        return $this->lockedPassword;
    }

    public function isMailNotified()
    {
        return $this->isMailNotified;
    }

    public function setLastUri($lastUri)
    {
        $this->lastUri = $lastUri;
    }

    public function getLastUri()
    {
        return $this->lastUri;
    }

    public function hasAcceptedComTerms()
    {
        return $this->hasAcceptedComTerms;
    }

    public function setAcceptedComTerms($boolean)
    {
        $this->hasAcceptedComTerms = $boolean;
    }

    /**
     * @param string $publicUrl
     *
     * @return User
     */
    public function setPublicUrl($publicUrl)
    {
        $this->publicUrl = $publicUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getPublicUrl()
    {
        return $this->publicUrl;
    }

    /**
     * @param mixed $hasTunedPublicUrl
     *
     * @return User
     */
    public function setHasTunedPublicUrl($hasTunedPublicUrl)
    {
        $this->hasTunedPublicUrl = $hasTunedPublicUrl;

        return $this;
    }

    /**
     * @return mixed
     */
    public function hasTunedPublicUrl()
    {
        return $this->hasTunedPublicUrl;
    }

    /**
     * @return \Claroline\CoreBundle\Entity\UserPublicProfilePreferences
     */
    public function getPublicProfilePreferences()
    {
        return $this->publicProfilePreferences;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\UserPublicProfilePreferences $publicProfilPreferences
     *
     * @return User
     */
    public function setPublicProfilePreferences(UserPublicProfilePreferences $publicProfilPreferences)
    {
        $publicProfilPreferences->setUser($this);

        $this->publicProfilePreferences = $publicProfilPreferences;

        return $this;
    }

    public function isPublicUrlValid(ExecutionContextInterface $context = null)
    {
        $return = true;
        $publicUrl = $this->getPublicUrl();
        // Search for whitespaces
        if ( $context && ! preg_match(USER::$patternUrlPublic, $this->getPublicUrl())) {
            $context->addViolationAt('publicUrl', 'public_profile_url_not_valid', array(), null);
        } elseif ( ! preg_match(USER::$patternUrlPublic, $publicUrl) )  {
            $return = false;
        } elseif ( ! $publicUrl ) {
            $return = false;
        }

        return $return;
    }

        public function isUserNameValid()
    {
        // Alphanumeric + dot
        if ( !preg_match(USER::$patternUrlPublic, $this->getUsername()) )  {
            return false;
        } else {
            return true;
        }
    }

    public function isFirstVisit()
    {
        return $this->isFirstVisit;
    }

    public function setFirstVisit($boolean)
    {
        $this->isFirstVisit = $boolean;
    }

    public function getMoocSessions()
    {
        return $this->moocSessions;
    }

    public function setMoocSessions(ArrayCollection $moocSessions)
    {
        $this->moocSessions = $moocSessions;
    }

    public function getIsValidate(){
        return $this->isValidate;
    }

    public function setIsValidate($isValidate){
        $this->isValidate = $isValidate;
    }

    public function getKeyValidate(){
        return $this->keyValidate;
    }

    public function setKeyValidate($keyValidate){
        $this->keyValidate = $keyValidate;
    }

    public function getSessionsByUsers(){
        return $this->sessionsByUsers;
    }

    public function setSessionsByUsers(ArrayCollection $sessionsByUsers){
        $this->sessionsByUsers = $sessionsByUsers;
    }

    public function isFacebookAccount(){
        return $this->isFacebookAccount;
    }

    public function setFacebookAccount($isFacebookAccount){
        $this->isFacebookAccount = $isFacebookAccount;
    }

    public function __toString() {
    	return "User(".$this->id.") : ".$this->username;
    }

    public function isRegisteredToSession(MoocSession $session) {
    	foreach ($this->moocSessions as $moocSession) {
    		if ($moocSession->getId() == $session->getId()) {
    			return true;
    		}
    	}

    	foreach ($this->groups as $group) {
    		foreach ($group->getMoocSessions() as $moocSession) {
    			if ($moocSession->getId() == $session->getId()) {
    				return true;
    			}
    		}
    	}

    	return false;
    }

    public function getNotifyWorkspaces() {
    	return $this->notifyWorkspaces;
    }

    public function hasGivenUpExercise(Exercise $exercise) {
    	foreach ($this->givenUpExercises as $exerciseUser) {
    		if ($exerciseUser->isGivenUp()
    				&& $exerciseUser->getExercise()->getId() == $exercise->getId()) {
    			return true;
    		}
    	}
    	return false;
    }

    public function getGender() { return $this->gender; }

    public function getCountry() { return $this->country; }

    public function getCity() { return $this->city; }

    public function getBirthdate() { return $this->birthdate; }

    public function getWebsite() { return $this->website; }

    public function getFacebook() { return $this->facebook; }

    public function getTwitter() { return $this->twitter; }

    public function getLinkedIn() { return $this->linkedIn; }

    public function getGooglePlus() { return $this->googlePlus; }

    public function setGender($gender) { $this->gender = $gender; }

    public function setCountry($country) { $this->country = $country; }

    public function setCity($city) { $this->city = $city; }

    public function setBirthdate($birthdate) { $this->birthdate = $birthdate; }

    public function setWebsite($website) { $this->website = $website; }

    public function setFacebook($facebook) { $this->facebook = $facebook; }

    public function setTwitter($twitter) { $this->twitter = $twitter; }

    public function setLinkedIn($linkedIn) { $this->linkedIn = $linkedIn; }

    public function setGooglePlus($googlePlus) { $this->googlePlus = $googlePlus; }

    public function getForumOrder() { return $this->forumOrder; }

    public function setForumOrder($forumOrder) { $this->forumOrder = $forumOrder; }

    public function getAge() {
    	if ($this->birthdate != null) {
	    	$now = new \DateTime();
	    	return $this->birthdate->diff($now)->y;
    	} else {
    		return 0;
    	}
    }

    public function getGenderLabel() {
    	if (self::GENDER_FEMALE == $this->gender) {
    		return "Femme";
    	} else if (self::GENDER_MALE == $this->gender) {
    		return "Homme";
    	} else {
    		return "";
    	}
    }

    public function setUserMoocPreferences(ArrayCollection $userMoocPreferences)
    {
        $this->userMoocPreferences = $userMoocPreferences;
    }

    public function getUserMoocPreferences() {

        return $this->userMoocPreferences;
    }

    public function checkSolerniPassword($password) {

        // First rule : 8 characters minimum
        if ( strlen($password) < 8 ) {
            return false;
        }

        // Second rule : one number minimum
        // Third rule : one maj character minimum
        // Fourth rule : one min character minimum
        // Firth rule : one symbol minimum
        if ( ! preg_match_all('$\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[\W_])\S*$', $password) ) {
            return false;
        }

        // Last rule : no space
        if ( ctype_space($password) ) {
            return false;
        }

        return true;

    }
}
