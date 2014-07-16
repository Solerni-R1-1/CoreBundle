<?php


namespace Claroline\CoreBundle\Controller\Mooc;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Entity\Mooc\SessionsByUsers;
use Claroline\CoreBundle\Entity\Mooc\MoocAccessConstraints;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Repository\UserRepository;
use Claroline\CoreBundle\Repository\Mooc\SessionsByUsersRepository;
/**
 * Mooc\MoocAccessConstraints service.
 */
class MoocAccessConstraintsService extends Controller {

	public function processUpgrade(array $constraints, User $user = null){

		$em = $this->getDoctrine()->getManager();
		
		$sessionsByUsersRepository = $em->getRepository('ClarolineCoreBundle:Mooc\SessionsByUsers');

		//Clean existing informations
		if(!empty($constraints)){
			foreach ($constraints as $constraint) {
				$this->container->get('logger')->error("delete  constraint n°".$constraint->getId()
					." and user n°".($user == null?'xx':$user->getId()));
				$sessionsByUsersRepository->deleteByConstraintIdAndUserId($constraint,$user); //delete for user_id AND constraintsID
			}
		} else if($user != null){
			$this->container->get('logger')->error("delete  user n°".$user->getId());
			$sessionsByUsersRepository->deleteByConstraintIdAndUserId(null, $user); //delete for user_id
		}

		if($user == null) {
			$userRepository = $em->getRepository('ClarolineCoreBundle:User');
			$users = $userRepository->findAll(); // get all users
		} else {
			$users = array($user);
		}

		if(empty($constraints)){
			$constraintsRepository = $em->getRepository('ClarolineCoreBundle:Mooc\MoocAccessConstraints');
			$constraints = $constraintsRepository->findAll(); // get all constraints
		}

		//Process the matching between constraint and user
		$matchings = array();
		foreach ($constraints as $constraint) {

			if(empty($constraint->getWhitelist()) && empty($constraint->getPatterns())){
				foreach ($users as $user) {
					$matchings = $this->addItem($matchings, $constraint, $user);
				}
				continue;
			}

			$whitelist = explode("\r\n", $constraint->getWhitelist());
			$patterns = explode("\r\n", $constraint->getPatterns());

			foreach ($users as $user) {

				$email = $user->getMail();
				if(in_array($email, $whitelist)){

$this->container->get('logger')->error($email." in ".print_r($whitelist, true));
					$matchings = $this->addItem($matchings, $constraint, $user);
					continue;
				}

				foreach($patterns as $pattern){
					//TODO change pattern for xxxx$/i
					if(!empty($pattern) && preg_match("/".$pattern."/i", $email)){
$this->container->get('logger')->error($email." match "."/".$pattern."/i");
						$matchings = $this->addItem($matchings, $constraint, $user);
						continue;
					}
				}
			}
		}

		$bucksize = 5;
		$i = 0;

		foreach ($constraints as $constraint) {

			if(!array_key_exists($constraint->getId(), $matchings)){
				continue;
			}
			$users = $matchings[$constraint->getId()];

			$owner = $constraint->getMoocOwner();
			if($owner == null){
				continue;
			}
			if($constraint->getMoocs() == null){
				continue;
			}
			foreach ($constraint->getMoocs() as $mooc) {
				//$this->container->get('logger')->error('MoocId :  '.$mooc->getId());
				if($mooc->getMoocSessions() == null){
					continue;
				}

				foreach($mooc->getMoocSessions() as $session ){
					//$this->container->get('logger')->error('SessionId :  '.$session->getId());
					foreach ($users as $user_id => $user) {

						/*$this->container->get('logger')->error('> '.$user->getId(). ','
							.$session->getId(). ','
							.$owner->getId(). ','
							.$constraint->getId());*/

						$item = new SessionsByUsers();
						$item->setUser($user);
						$item->setMoocSession($session);
						$item->setMoocOwner($owner);
						$item->setMoocAccessConstraints($constraint);


						$i++;
						//TODO : decomment here
						$em->persist($item);
						if (($i % $bucksize) == 0) {
					         $em->flush();
					    }
					}
				}
			}
		}


        $em->flush();
        //$em->clear();

		//$this->container->get('logger')->error("TOTAL : $i");

	}

	public function processDelete(array $constraints, User $user = null){
		$em = $this->getDoctrine()->getManager();
		
		$sessionsByUsersRepository = $em->getRepository('ClarolineCoreBundle:Mooc\SessionsByUsers');

		//Clean existing informations
		if(!empty($constraints)){
			foreach ($constraints as $constraint) {
				$this->container->get('logger')->error("delete  constraint n°".$constraint->getId()
					." and user n°".($user == null?'xx':$user->getId()));
				$sessionsByUsersRepository->deleteByConstraintIdAndUserId($constraint,$user); //delete for user_id AND constraintsID
			}
		} else if($user != null){
			$this->container->get('logger')->error("delete  constraint n°".$constraint->getId());
			$sessionsByUsersRepository->deleteByConstraintIdAndUserId(null, $user); //delete for user_id
		}
	}

	private function addItem(array $matchings, MoocAccessConstraints $constraint, User $user){

		if(!array_key_exists($constraint->getId(), $matchings)){
			$matchings[$constraint->getId()] = array();
		}

		if(!array_key_exists($user->getId(), $matchings[$constraint->getId()])){
			$matchings[$constraint->getId()][$user->getId()] = $user;
		}

		return $matchings;
	}
}


?>