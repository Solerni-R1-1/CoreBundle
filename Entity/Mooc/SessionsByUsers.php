<?php

namespace Claroline\CoreBundle\Entity\Mooc;

use Doctrine\ORM\Mapping as ORM;

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
     * @ORM\OneToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     */
    private $user;

    /**
     * @var Claroline\CoreBundle\Entity\Mooc\MoocSession
     *
     * @ORM\OneToOne(targetEntity="Claroline\CoreBundle\Entity\Mooc\MoocSession")
     */
    private $moocSession;

    /**
     * @var Claroline\CoreBundle\Entity\Mooc\MoocOwner
     *
     * @ORM\OneToOne(targetEntity="Claroline\CoreBundle\Entity\Mooc\MoocOwner")
     */
    private $moocOwner;
    

    /**
     * @var Claroline\CoreBundle\Entity\Mooc\MoocAccessConstraints
     *
     * @ORM\OneToOne(targetEntity="Claroline\CoreBundle\Entity\Mooc\MoocAccessConstraints")
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

    public function setUser(Claroline\CoreBundle\Entity\User $user) {
        $this->user = $user;
    }

    public function setMoocSession(Claroline\CoreBundle\Entity\Mooc\MoocSession $moocSession) {
        $this->moocSession = $moocSession;
    }

    public function setMoocOwner(Claroline\CoreBundle\Entity\Mooc\MoocOwner $moocOwner) {
        $this->moocOwner = $moocOwner;
    }

    public function setMoocAccessConstraints(Claroline\CoreBundle\Entity\Mooc\MoocAccessConstraints $moocAccessConstraints) {
        $this->moocAccessConstraints = $moocAccessConstraints;
    }

}
