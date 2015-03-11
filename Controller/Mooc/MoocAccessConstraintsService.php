<?php

namespace Claroline\CoreBundle\Controller\Mooc;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Entity\Mooc\SessionsByUsers;
use Claroline\CoreBundle\Entity\Mooc\MoocAccessConstraints;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Repository\UserRepository;
use Claroline\CoreBundle\Repository\Mooc\SessionsByUsersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Repository\Mooc\MoocAccessConstraintsRepository;


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
    		if ($constraint->getWhitelist() != "") {
    			$whiteListArray = explode("\r\n", $constraint->getWhitelist());
    		} else {
    			$whiteListArray = array();
    		}
    		if ($constraint->getPatterns() != "") {
    			$patternsArray = explode("\r\n", $constraint->getPatterns());
    		} else {
    			$patternsArray = array();
    		}

    		if (count($patternsArray) > 0 || count($whiteListArray) > 0) {
	    		$users = $userRepository->findByMailInOrLike($whiteListArray, $patternsArray);
    		} else {
    			$users = array();
    		}
    		//die("a");
	    	$this->setNewUsersForConstraint($constraint, $users);
    	}
    }
    
    public function setNewUsersForConstraint(MoocAccessConstraints $constraint, array $users) {
    	// Init
    	$em = $this->getDoctrine()->getManager();
    	/* @var $roleManager RoleManager */
    	$roleManager = $this->container->get('claroline.manager.role_manager');
    	
    	$oldSessionsByUsers = $constraint->getSessionsByUsers();
    	$sessions = array();

    	if ($constraint->getMoocs() != null) {
	    	foreach ($constraint->getMoocs() as $mooc) {
	    		foreach ($mooc->getMoocSessions() as $session) {
	    			$sessions[] = $session;
	    		}
	    	}
    	}
    	
    	if ($oldSessionsByUsers != null) {
	    	foreach ($oldSessionsByUsers as $i => $oldSessionByUser) {
    			$collaboratorRole = $roleManager->getCollaboratorRole($oldSessionByUser->getMoocSession()->getMooc()->getWorkspace());
    			$oldMoocSession = $oldSessionByUser->getMoocSession();
    			$user = $oldSessionByUser->getUser();
    			
	    		if (!in_array($oldMoocSession, $sessions) || !in_array($user, $users)) {
	    			if (!$user->getMoocSessions()->contains($oldMoocSession)) {
	    				$user->removeRole($collaboratorRole);
	    			}
	    			unset($oldSessionsByUsers[$i]);
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
    		foreach ($sessions as $session) {
    	
    			$collaboratorRole = $roleManager->getCollaboratorRole($session->getMooc()->getWorkspace());
	    		$newSession = new SessionsByUsers();
	    		
	    		$newSession->setMoocAccessConstraints($constraint);
	    		$newSession->setUser($user);
	    		$newSession->setMoocSession($session);
	    		$newSession->setMoocOwner($constraint->getMoocOwner());
	    		$oldSessionsByUsers->add($newSession);
	    		$em->persist($newSession);
	    		
	    		$user->addRole($collaboratorRole);
	    		$em->persist($user);
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
    
    public function refreshDeletedConstraintForWorkspace(AbstractWorkspace $workspace) {
    	if ($workspace->getMooc() != null) {
	    	/* @var $roleManager RoleManager */
	    	$roleManager = $this->container->get('claroline.manager.role_manager');
    		$em = $this->getDoctrine()->getManager();
    		/* @var $constraintsRepo MoocAccessConstraintsRepository */
    		$constraintsRepo = $em->getRepository('ClarolineCoreBundle:Mooc\MoocAccessConstraints');
    		$constraints = $constraintsRepo->findByMooc($workspace->getMooc(), $workspace->getMooc()->getAccessConstraints()->toArray());
    		
    		
    		foreach ($constraints as $constraint) {
    			foreach ($constraint->getSessionsByUsers() as $sessionByUser) {
    				$moocSession = $sessionByUser->getMoocSession();
    				if ($workspace->getMooc()->getMoocSessions()->contains($moocSession)) {
	    				$user = $sessionByUser->getUser();
	    				$collaboratorRole = $roleManager->getCollaboratorRole($moocSession->getMooc()->getWorkspace());
		    			if (!$user->getMoocSessions()->contains($moocSession)) {
		    				$user->removeRole($collaboratorRole);
		    			}
	    				$em->remove($sessionByUser);
    				}
    			}
    		}
    	}
    }
}
