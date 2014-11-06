<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager\Contact;

use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Repository\Contact\ContactRepository;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Entity\Contact\Contact;

/**
 * @DI\Service("claroline.manager.contact_manager")
 */
class ContactManager
{
    private $om;
    /** @var ContactRepository */
    private $contactRepo;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     * })
     */
    public function __construct(
        ObjectManager $om
    )
    {
        $this->om = $om;
        $this->contactRepo = $om->getRepository('ClarolineCoreBundle:Contact\Contact');
    }

    /**
     * Persists and flush a contact.
     *
     * @param \Claroline\CoreBundle\Entity\Contact\Contact $contact
     */
    public function updateContact(Contact $contact)
    {
        $this->om->persist($contact);
        $this->om->flush();
    }

    /**
     * Removes a contact.
     *
     * @param \Claroline\CoreBundle\Entity\Contact\Contact $contact
     */
    public function deleteContact(Contact $contact)
    {
        $this->om->remove($contact);
        $this->om->flush();
    }


    /**
     * @return Contact[]
     */
    public function getAllContacts()
    {
        return $this->contactRepo->findAll();

    }


}
