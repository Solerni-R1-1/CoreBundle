<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Mooc;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Mooc\Mooc;
use Claroline\ForumBundle\Repository\Mooc\UserMoocPreferencesRepository;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * @ORM\Entity
 * @ORM\Table(name="claro_user_mooc_preferences")
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Mooc\UserMoocPreferencesRepository")
 */
class UserMoocPreferences
{

    /**
     * @ORM\Id
     * @ORM\ManyToOne(
     *      targetEntity="Claroline\CoreBundle\Entity\Mooc\Mooc"
     * )
     */
    protected $mooc;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(
     *      targetEntity="Claroline\CoreBundle\Entity\User",
     *      inversedBy="userMoocPreferences"
     * )
     */
    protected $user;
    
    /**
     * @ORM\Column(type="boolean")
     */
    protected $visibility;

    /**
     * Set mooc
     *
     * @param Mooc $mooc
     * @return userMoocPreferences
     */
    public function setMooc(Mooc $mooc)
    {
        $this->mooc = $mooc;

        return $this;
    }

    /**
     * Get mooc
     *
     * @return \Claroline\CoreBundle\Entity\Mooc\Mooc
     */
    public function getMooc()
    {
        return $this->mooc;
    }

    /**
     * Set user
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     * @return userMoocPreferences
     */
    public function setUser( User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Claroline\CoreBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
    
    /**
     * Get mooc public visibility
     *
     * @return boolean 
     */
    public function getVisibility() {
        return $this->visibility;
    }

    /**
     * Set mooc public visibility
     *
     * @param boolean
     * @return userMoocPreferences 
     */
    public function setVisibility( $boolean ) {
               
        $this->visibility = $boolean;
        
        return $this;
    }


}
