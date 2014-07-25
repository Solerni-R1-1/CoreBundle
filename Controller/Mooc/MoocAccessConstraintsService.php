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
class MoocAccessConstraintsService extends Controller
{

    public function processUpgrade(array $constraints, User $user = null)
    {

        $em = $this->getDoctrine()->getManager();
        $sessionsByUsersRepository = $em->getRepository('ClarolineCoreBundle:Mooc\SessionsByUsers');

        //Clean existing informations
        if ( ! empty( $constraints ) ) {
            foreach ( $constraints as $constraint ) {
                $this->container
                     ->get('logger')->info("Delete rules for constraint n°" . $constraint->getId()
                        . " and user n°" . ($user == null ? '(any)' : $user->getId()));
                $sessionsByUsersRepository->deleteByConstraintIdAndUserId($constraint, $user); //delete for user_id AND constraintsID
            }
        } else if ( $user != null ) {
            $this->container->get('logger')->info("delete rules for user n°" . $user->getId());
            $sessionsByUsersRepository->deleteByConstraintIdAndUserId(null, $user); //delete for user_id
        }

        if ( $user == null ) {
            $userRepository = $em->getRepository('ClarolineCoreBundle:User');
            $users = $userRepository->findAll(); // get all users
        } else {
            $users = array($user);
        }

        if ( empty($constraints) ) {
            $constraintsRepository = $em->getRepository('ClarolineCoreBundle:Mooc\MoocAccessConstraints');
            $constraints = $constraintsRepository->findAll(); // get all constraints
        }

        //Process the matching between constraint and user
        $matchings = array();
        foreach ( $constraints as $constraint ) {
            $whiteListString = $constraint->getWhitelist();
            $patternsString = $constraint->getPatterns();
            
            /* if empty -> all user have rights. Maybe no users instead ? */
            if ( empty($whiteListString) && empty($patternsString) ) {
                foreach ($users as $user) {
                    $matchings = $this->addItem($matchings, $constraint, $user);
                }
                continue;
            }

            $whitelist = explode("\r\n", $whiteListString);
            $patterns = explode("\r\n", $patternsString);

            foreach ($users as $user) {

                $email = $user->getMail();
                if (in_array($email, $whitelist)) {

                    $this->container->get('logger')->info('Match found: ' . $email . " in " . print_r($whitelist, true));
                    $matchings = $this->addItem($matchings, $constraint, $user);
                    continue;
                }

                foreach ($patterns as $pattern) {
                    //TODO change pattern for xxxx$/i - 
                    if (!empty($pattern) && preg_match("/" . $pattern . "/i", $email)) {
                        $this->container->get('logger')->info($email . " match " . "/" . $pattern . "/i");
                        $matchings = $this->addItem($matchings, $constraint, $user);
                        continue;
                    }
                }
            }
        }

        $bucksize = 5;
        $i = 0;

        foreach ($constraints as $constraint) {
            
            if (!array_key_exists($constraint->getId(), $matchings)) {
                continue;
            }
            $users = $matchings[$constraint->getId()];

            $owner = $constraint->getMoocOwner();
            
            if ($owner == null) {
                $this->container->get('logger')->error('This constraint has no owner. Match is invalid');
                continue;
            }
            $this->container->get('logger')->info('Owner found:  '.$owner->getId());
            
            if ($constraint->getMoocs() == null) {
                $this->container->get('logger')->error('This constraint has no mooc. Match is invalid');
                continue;
            }
            
            foreach ($constraint->getMoocs() as $mooc) {
                $this->container->get('logger')->info('MoocId found:  '.$mooc->getId());

                if ($mooc->getMoocSessions() == null) {
                    $this->container->get('logger')->error('The mooc has no session. Match is invalid');
                    continue;
                }

                foreach ($mooc->getMoocSessions() as $session) {
                    $this->container->get('logger')->info('SessionId found:  '.$session->getId());
                    foreach ($users as $user_id => $user) {
                        
                        $this->container->get('logger')->info('Generated rule (user, session, owner, constraint) > '.$user->getId(). ','
                          .$session->getId(). ','
                          .$owner->getId(). ','
                          .$constraint->getId());
                        
                        /* adding role to the user for the workspace */
                        
                        $workspace = $mooc->getWorkspace();
                        $roleManager = $this->container->get('claroline.manager.role_manager');
                        $userRoles = $user->getEntityRoles();
                        
                        if ( ! $userRoles->contains($roleManager->getManagerRole($workspace)) &&
                             ! $userRoles->contains($roleManager->getCollaboratorRole($workspace)) &&
                             ! $userRoles->contains($roleManager->getRoleByName('ROLE_ADMIN'))) {
                            
                            $role = $roleManager->getCollaboratorRole($workspace);
                            $user->addRole($role); // does this flush ?
                        }
                        
                        /* create new session autorization for the user */
                        $item = new SessionsByUsers();
                        $item->setUser($user);
                        $item->setMoocSession($session);
                        $item->setMoocOwner($owner);
                        $item->setMoocAccessConstraints($constraint);

                        $i++;
                        $em->persist($item);
                        if (($i % $bucksize) == 0) {
                            //$this->container->get('logger')->info("Flush $i");
                            $em->flush();
                        }
                    }
                }
            }
            
        }
        //$this->container->get('logger')->info("EntityManager#Flush");
        $em->flush();
    }

    public function processDelete(array $constraints, User $user = null)
    {
        $em = $this->getDoctrine()->getManager();

        $sessionsByUsersRepository = $em->getRepository('ClarolineCoreBundle:Mooc\SessionsByUsers');

        //Clean existing informations
        if (!empty($constraints)) {
            foreach ($constraints as $constraint) {
                $this->container->get('logger')->error("delete  constraint n°" . $constraint->getId()
                        . " and user n°" . ($user == null ? 'xx' : $user->getId()));
                $sessionsByUsersRepository->deleteByConstraintIdAndUserId($constraint, $user); //delete for user_id AND constraintsID
            }
        } else if ($user != null) {
            $this->container->get('logger')->error("delete  constraint n°" . $constraint->getId());
            $sessionsByUsersRepository->deleteByConstraintIdAndUserId(null, $user); //delete for user_id
        }
    }

    private function addItem(array $matchings, MoocAccessConstraints $constraint, User $user)
    {

        if (!array_key_exists($constraint->getId(), $matchings)) {
            $matchings[$constraint->getId()] = array();
        }

        if (!array_key_exists($user->getId(), $matchings[$constraint->getId()])) {
            $matchings[$constraint->getId()][$user->getId()] = $user;
        }

        return $matchings;
    }

}
