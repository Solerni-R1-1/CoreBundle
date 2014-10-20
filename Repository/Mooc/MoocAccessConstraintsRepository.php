<?php

namespace Claroline\CoreBundle\Repository\Mooc;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Mooc\MoocAccessConstraints;

/**
 * MoocAccessConstraintsRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MoocAccessConstraintsRepository extends EntityRepository
{

	public function findByUserMail($mail) {
		$domain = substr($mail, strrpos($mail, '@'));
		echo $domain."<br />";
		
		$dql = "SELECT mac FROM Claroline\CoreBundle\Entity\Mooc\MoocAccessConstraints mac
				WHERE mac.patterns LIKE :start_domain
				OR mac.patterns LIKE :middle_domain
				OR mac.patterns LIKE :end_domain
				
				OR mac.whitelist LIKE :start_mail
				OR mac.whitelist LIKE :middle_mail
				OR mac.whitelist LIKE :end_mail";
		$query = $this->_em->createQuery($dql);
		$query->setParameter("start_mail", $mail."\n%");
		$query->setParameter("middle_mail", "%\n".$mail."\n%");
		$query->setParameter("end_mail", "%\n".$mail);
		
		$query->setParameter("start_domain", $domain."\n%");
		$query->setParameter("middle_domain", "%\n".$domain."\n%");
		$query->setParameter("end_domain", "%\n".$domain);
		
		return $query->getResult();
	}
}
