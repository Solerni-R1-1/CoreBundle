<?php

namespace Claroline\CoreBundle\Controller\Mooc;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Entity\Mooc\SessionsByUsers;
use Claroline\CoreBundle\Entity\Mooc\MoocAccessConstraints;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Repository\UserRepository;
use Claroline\CoreBundle\Repository\Mooc\SessionsByUsersRepository;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * Mooc\MoocAccessConstraints service.
 */
class MoocAccessConstraintsService extends Controller
{
	public function processUpgradeUsers(array $users) {
    	$em = $this->getDoctrine()->getManager();
        $constraintsRepository = $em->getRepository('ClarolineCoreBundle:Mooc\MoocAccessConstraints');
    	
    	foreach ($users as $user) {
    		/* @var $user User */
        	$constraints = $constraintsRepository->findByUserMail($user->getMail());
    		$sessionsByUsers = $user->getSessionsByUsers();
    		foreach ($sessionsByUsers as $i => $sessionByUser) {
    			/* @var $sessionByUser SessionsByUsers */
    			if (!in_array($sessionByUser->getMoocAccessConstraints(), $constraints)) {
    				unset($sessionsByUsers[$i]);
    				$em->remove($sessionByUser);
    			} else {
    				unset($constraints[array_search($sessionByUser, $constraints)]);
    			}
    		}
    		
    		foreach ($constraints as $constraint) {
    			foreach ($constraint->getMoocs() as $mooc) {
    				foreach ($mooc->getMoocSessions() as $session) {
			    		$newSession = new SessionsByUsers();
			    		
			    		$newSession->setMoocAccessConstraints($constraint);
			    		$newSession->setUser($user);
			    		$newSession->setMoocSession($session);
			    		$newSession->setMoocOwner($constraint->getMoocOwner());
			    		$sessionsByUsers->add($newSession);
			    		$em->persist($newSession);
    				}
    			}
    		}
    		$em->persist($user);
    		$em->flush();
    	}
    }
    
    public function processUpgradeConstraints(array $constraints) {
        $em = $this->getDoctrine()->getManager();
    	$userRepository = $em->getRepository('ClarolineCoreBundle:User');
    	foreach ($constraints as $constraint) {
    		/* @var $constraint MoocAccessConstraints */
    		$whiteListArray = explode("\n", $constraint->getWhitelist());
    		$patternsArray = explode("\n", $constraint->getPatterns());
    	
    		$constraintsUsers[$constraint->getId()] = $userRepository->findByMailInOrLike($whiteListArray, $patternsArray);
    		$this->setNewUsersForConstraint($constraint, $constraintsUsers[$constraint->getId()]);
    	}
    }
    
    public function setNewUsersForConstraint(MoocAccessConstraints $constraint, array $users) {
    	$em = $this->getDoctrine()->getManager();
    	$oldSessionsByUsers = $constraint->getSessionsByUsers();
    	$sessions = array();
    	foreach ($constraint->getMoocs() as $mooc) {
    		foreach ($mooc->getMoocSessions() as $session) {
    			$sessions[] = $session;
    		}
    	}
    	
    	if ($oldSessionsByUsers != null) {
	    	foreach ($oldSessionsByUsers as $i => $oldSessionByUser) {
	    		if (!in_array($oldSessionByUser->getMoocSession(), $sessions)) {
	    			unset($oldSessionsByUsers[$i]);
	    			$em->remove($oldSessionByUser);
	    		} else
	    		if (!in_array($oldSessionByUser->getUser(), $users)) {
	    			unset ($oldSessionsByUsers[$i]);
	    			$em->remove($oldSessionByUser);
	    		} else {
	    			unset($users[array_search($oldSessionByUser->getUser(), $users)]);
	    		}
	    	}
    	} else {
    		$oldSessionsByUsers = new ArrayCollection();
    		$constraint->setSessionsByUsers($oldSessionsByUsers);
    	}
    	
    	foreach ($users as $user) {
    		if ($constraint->getMoocs() != null) {
	    		foreach ($sessions as $session) {
		    		$newSession = new SessionsByUsers();
		    		
		    		$newSession->setMoocAccessConstraints($constraint);
		    		$newSession->setUser($user);
		    		$newSession->setMoocSession($session);
		    		$newSession->setMoocOwner($constraint->getMoocOwner());
		    		$oldSessionsByUsers->add($newSession);
		    		$em->persist($newSession);
	    		}
    		}
    	}
	    $em->persist($constraint);
	    $em->flush();
    }

    public function processDelete(array $constraints, User $user = null)
    {
    	$logger = $this->getLogger();
        $em = $this->getDoctrine()->getManager();
        $sessionsByUsersRepository = $em->getRepository('ClarolineCoreBundle:Mooc\SessionsByUsers');

        //Clean existing informations
        if (!empty($constraints)) {
            foreach ($constraints as $constraint) {
                $logger->error("delete  constraint n°" . $constraint->getId()
                        . " and user n°" . ($user == null ? 'xx' : $user->getId()));
                $sessionsByUsersRepository->deleteByConstraintIdAndUserId($constraint, $user); //delete for user_id AND constraintsID
            }
        } else if ($user != null) {
            $logger->error("delete  constraint n°" . $constraint->getId());
            $sessionsByUsersRepository->deleteByConstraintIdAndUserId(null, $user); //delete for user_id
        }
    }
    
    private function getLogger() {
        return $this->container->get('logger');
    }
}
