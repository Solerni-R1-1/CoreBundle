<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Contact;

use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Claroline\CoreBundle\Entity\AbstractRoleSubject;
use Claroline\CoreBundle\Entity\OrderableInterface;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Contact\ContactRepository")
 * @ORM\Table(
 *      name="claro_contact",
*       uniqueConstraints={
 *          @ORM\UniqueConstraint(name="contact_unique_name", columns={"name"})
 *      }
 *  )
 * @DoctrineAssert\UniqueEntity("name")
 */
class Contact extends AbstractRoleSubject implements OrderableInterface
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
    protected $name;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     */
    protected $mail;


    public function __construct()
    {
        parent::__construct();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
    
    public function setMail($mail)
    {
        $this->mail = $mail;
    }

    public function getMail()
    {
        return $this->mail;
    }

    public function getOrderableFields()
    {
        return array('name', 'id');
    }

}
