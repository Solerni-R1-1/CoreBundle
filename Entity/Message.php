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

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\MessageRepository")
 * @ORM\Table(name="claro_message")
 */
class Message
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     */
    protected $object;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    protected $content;

    /**
     * @todo rename the property to "sender"
     *
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="sender_id", onDelete="CASCADE", nullable=true)
     */
    protected $user;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    protected $date;

    /**
     * @ORM\Column(name="is_removed", type="boolean")
     */
    protected $isRemoved;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\UserMessage",
     *     mappedBy="message"
     * )
     */
    protected $userMessages;

    /**
    * @ORM\ManyToOne(
    *     targetEntity="Claroline\CoreBundle\Entity\Message"
    * )
    * @ORM\JoinColumn(name="root", referencedColumnName="id", onDelete="SET NULL")
    */
    protected $root;


    /**
     * @ORM\Column(name="sender_username")
     */
    protected $senderUsername = 'claroline-connect';

    /**
     * @ORM\Column(name="receiver_string", length=1023)
     */
    protected $to;

    public function __construct()
    {
        $this->isRemoved = false;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getObject()
    {
        return $this->object;
    }

    public function setObject($object)
    {
        $this->object = $object;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getSender()
    {
        return $this->user;
    }

    public function setSender($sender)
    {
        $this->user = $sender;
        $this->senderUsername = ($sender) ? $sender->getUsername(): 'claroline-connect';
    }

    public function getDate()
    {
        return $this->date;
    }

    /**
     * Sets the message creation date.
     *
     * NOTE : creation date is already handled by the timestamp listener; this
     *        setter exists mainly for testing purposes.
     *
     * @param \DateTime $date
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;
    }

    public function isRemoved()
    {
        return $this->isRemoved;
    }

    public function markAsRemoved()
    {
        $this->isRemoved = true;
    }

    public function markAsUnremoved()
    {
        $this->isRemoved = false;
    }

    public function getUserMessages()
    {
        return $this->userMessages;
    }

    public function getRoot()
    {
        return $this->root;
    }

    public function setRoot($root){
        $this->root = $root;
    }

    public function getTo()
    {
        return $this->to;
    }

    public function setTo($to)
    {
        $this->to = $to;
    }

    public function getSenderUsername()
    {
        return $this->senderUsername;
    }
}
