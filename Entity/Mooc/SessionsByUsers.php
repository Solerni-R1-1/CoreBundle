<?php

namespace Claroline\CoreBundle\Entity\Mooc;

use Doctrine\ORM\Mapping as ORM;
use Claroline\CoreBundle\Entity\Mooc\MoocAccessConstraints;
use Claroline\CoreBundle\Entity\Mooc\MoocOwner;
use Claroline\CoreBundle\Entity\Mooc\MoocSession;
use Claroline\CoreBundle\Entity\User;

/**
 * MoocOwner
 *
 * @ORM\Table(name="claro_mooc_sessions_by_users")
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Mooc\SessionsByUsersRepository")
 */
class SessionsByUsers
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
     * @var Claroline\CoreBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @var Claroline\CoreBundle\Entity\Mooc\MoocSession
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Mooc\MoocSession", cascade={"persist"})
     * @ORM\JoinColumn(name="moocSession_id", referencedColumnName="id")
     */
    private $moocSession;

    /**
     * @var Claroline\CoreBundle\Entity\Mooc\MoocOwner
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Mooc\MoocOwner", cascade={"persist"})
     * @ORM\JoinColumn(name="moocOwner_id", referencedColumnName="id")
     */
    private $moocOwner;
    

    /**
     * @var Claroline\CoreBundle\Entity\Mooc\MoocAccessConstraints
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Mooc\MoocAccessConstraints", cascade={"persist"})
     * @ORM\JoinColumn(name="moocAccessConstraints_id", referencedColumnName="id")
     */
    private $moocAccessConstraints;
    
    
    /* GETTERS/SETTERS */

    public function getId() {
        return $this->id;
    }

    public function getUser() {
        return $this->user;
    }

    public function getMoocSession() {
        return $this->moocSession;
    }

    public function getMoocOwner() {
        return $this->moocOwner;
    }

    public function getMoocAccessConstraints() {
        return $this->moocAccessConstraints;
    }

    public function setUser(User $user) {
        $this->user = $user;
    }

    public function setMoocSession(MoocSession $moocSession) {
        $this->moocSession = $moocSession;
    }

    public function setMoocOwner(MoocOwner $moocOwner) {
        $this->moocOwner = $moocOwner;
    }

    public function setMoocAccessConstraints(MoocAccessConstraints $moocAccessConstraints) {
        $this->moocAccessConstraints = $moocAccessConstraints;
    }

}
