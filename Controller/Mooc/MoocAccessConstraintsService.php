<?php


namespace Claroline\CoreBundle\Controller\Mooc;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Entity\Mooc\SessionsByUsers;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Repository\UserRepository;
use Claroline\CoreBundle\Repository\Mooc\SessionsByUsersRepository;
/**
 * Mooc\MoocAccessConstraints service.
 */
class MoocAccessConstraintsService extends Controller {

	public function process(array $constraints, User $user = null){


		$this->container->get('logger')->error("test4");
		$em = $this->getDoctrine()->getManager();
		
		$sessionsByUsersRepository = $em->getRepository('ClarolineCoreBundle:Mooc\SessionsByUsers');
		//getRepository('ClarolineCoreBundle:Mooc\MoocAccessConstraints')
$this->container->get('logger')->error("test5");
		//Clean existing informations
		if(!empty($constraints)){
			foreach ($constraints as $constraint) {
				$sessionsByUsersRepository->deleteByConstraintIdAndUserId($constraint,$user); //delete for user_id AND constraintsID
			}
		} else if($user != null){
			$sessionsByUsersRepository->deleteByConstraintIdAndUserId(null, $user); //delete for user_id
		}
$this->container->get('logger')->error("test6");

		if($user == null) {
			$userRepository = $em->getRepository('ClarolineCoreBundle:User');
			$users = $userRepository->findAll(); // get all users
		} else {
			$users = array($user);
		}
$this->container->get('logger')->error("test7");
		if(empty($constraints)){
			$constraintsRepository = $em->getRepository('ClarolineCoreBundle:Mooc\MoocAccessConstraints');
			$constraints = $constraintsRepository->findAll(); // get all constraints
		}
$this->container->get('logger')->error("test8");
		//Process the matching between constraint and user
		$matchings = array();
		foreach ($constraints as $constraint) {

			if(array_key_exists($constraint->getId(), $matchings)){
				$matchings[$constraint->getId()] = array();
			}

			if(empty($constraint->getWhitelist()) && empty($constraint->getPatterns())){
				$matchings[$constraint->getId()][$user->getId()] = $user;
				continue;
			}

			$whitelist = explode("\r\n", $constraint->getWhitelist());
			$patterns = explode("\r\n", $constraint->getPatterns());

			foreach ($users as $user) {

				$email = $user->getEmail();
				if(in_array($email, $whitelist)){
					$matchings[$constraint->getId()][$user->getId()] = $user;
					continue;
				}

				foreach($patterns as $pattern){
					//TODO change pattern for xxxx$/i
					if(preg_match("/".$pattern."/i", $email)){
						$matchings[$constraint->getId()][$user->getId()] = $user;
						continue;
					}
				}
			}
		}

		$bucksize = 100;
		$i = 0;
		$items = array();
		foreach ($constraints as $constraint) {
			foreach ($constraint->getMoocOwner() as $owner) {
				foreach ($constraint->getMoocs() as $mooc) {
					foreach($mooc->getSessions() as $session ){
						foreach ($matchings[$constraint->getId()] as $user_id => $user) {
							$item = new SessionsByUser();
							$item->setUser($user);
							$item->setMoocSession($session);
							$item->setMoocOwner($owner);
							$item->setMoocAccessConstraints($constraint);
							//DOTO comment here
							$items[] = $item;
							$i++;
							//TODO : decomment here
							//_$sessionsByUsersRepository->persist($item)
							//if (($i % $bucksize) == 0) {
						    //     $sessionsByUsersRepository->flush();
						    //     $sessionsByUsersRepository->clear();
						    //}
						}
					}
				}
			}
		}

	}
}


?>