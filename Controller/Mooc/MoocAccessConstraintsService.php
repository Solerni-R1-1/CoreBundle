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
        
        if(empty($constraints) && empty($user)){
            $this->getLogger()->info('Both $constraints and $user are NULL. End of processUpgrade()');
            return;
        }
        
        $em = $this->getDoctrine()->getManager();
        $sessionsByUsersRepository = $em->getRepository('ClarolineCoreBundle:Mooc\SessionsByUsers');
        $constraintOriginalUsers = array();
        $constraintNewUsers = array();
        
        //Clean existing informations
        if ( ! empty( $constraints ) ) {
           
            foreach ( $constraints as $constraint ) {
                
                // Store current users of the contraint to check before output if someone was erased
                foreach( $constraint->getSessionsByUsers() as $sessionByUser ) {
                    $beforeUser = $sessionByUser->getUser();
                    $constraintOriginalUsers[$constraint->getId()][$beforeUser->getId()] = $beforeUser;
                }

                $this->getLogger()->info("**** Case entity is Constraint : clean rules for constraint ID " . $constraint->getId()
                        . " and user n°" . ($user == null ? '(any)' : $user->getId()));
                //delete for user_id AND constraintsID
                $sessionsByUsersRepository->deleteByConstraintIdAndUserId($constraint, $user); 
            }
        } else if ( $user != null ) {
            $this->getLogger()->info("**** Case entity is user : clean rules for user ID " . $user->getId());
            // Store current users of the contraint to check before output if someone was erased
            foreach ( $user->getSessionsByUsers() as $UserSession ) {
                $constraintOriginalUsers[$UserSession->getMoocAccessConstraints()->getId()][$user->getId()] = $user;
            }
            
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

                    $this->getLogger()->info('Match found: ' . $email . " in " . print_r($whitelist, true));
                    $matchings = $this->addItem($matchings, $constraint, $user);
                    continue;
                }

                foreach ($patterns as $pattern) {
                    if (!empty($pattern) && preg_match("/" . $pattern . "$/i", $email)) {
                        $this->getLogger()->info($email . " match " . "/" . $pattern . "$/i");
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
                $this->getLogger()->error('This constraint has no owner. Match is invalid');
                continue;
            }
            //$this->getLogger()->info('Owner found:  '.$owner->getId());
            
            if ($constraint->getMoocs() == null) {
                $this->getLogger()->error('This constraint has no mooc. Match is invalid');
                continue;
            }
            
            foreach ($constraint->getMoocs() as $mooc) {
                $this->getLogger()->info('Mooc ID:  '.$mooc->getId());

                if ($mooc->getMoocSessions() == null) {
                    $this->getLogger()->error('The mooc has no session. Match is invalid');
                    continue;
                }

                foreach ($mooc->getMoocSessions() as $session) {
                    $this->getLogger()->info('Session ID:  '.$session->getId());
                    foreach ($users as $user_id => $user) {
                        
                        $this->getLogger()->info('Generated rule (user, session, owner, constraint) : '.$user->getId(). ','
                          .$session->getId(). ','
                          .$owner->getId(). ','
                          .$constraint->getId());
                        
                        /* create new session autorization for the user */
                        $item = new SessionsByUsers();
                        $item->setUser($user);
                        $item->setMoocSession($session);
                        $item->setMoocOwner($owner);
                        $item->setMoocAccessConstraints($constraint);

                        $i++;
                        $em->persist($item);
                        if (($i % $bucksize) == 0) {
                            //$this->getLogger()->info("Flush $i");
                            $em->flush();
                        }
                    }
                }
            }
            // Flush for entity beyond bucksize
            $em->flush();
        }
        //$this->getLogger()->info("EntityManager#Flush");
        //$em->flush();
        
        // Store current users of each constraint to check if someone was added
        foreach( $constraints as $constraint ) {
            $em->refresh($constraint);
            foreach( $constraint->getSessionsByUsers() as $sessionByUser ) {
                $afterUser = $sessionByUser->getUser();
                $constraintNewUsers[$constraint->getId()][$afterUser->getId()] = $afterUser;
            }
        }
        
        // refresh users role
        $this->addRemoveUsersRoles($constraintOriginalUsers, $constraintNewUsers, $constraints );
    }

    public function processDelete(array $constraints, User $user = null)
    {
        $em = $this->getDoctrine()->getManager();
        $sessionsByUsersRepository = $em->getRepository('ClarolineCoreBundle:Mooc\SessionsByUsers');

        //Clean existing informations
        if (!empty($constraints)) {
            foreach ($constraints as $constraint) {
                $this->getLogger()->error("delete  constraint n°" . $constraint->getId()
                        . " and user n°" . ($user == null ? 'xx' : $user->getId()));
                $sessionsByUsersRepository->deleteByConstraintIdAndUserId($constraint, $user); //delete for user_id AND constraintsID
            }
        } else if ($user != null) {
            $this->getLogger()->error("delete  constraint n°" . $constraint->getId());
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
    /*
     * compares two arrays : before and after 
     * and update users roles
     */
    private function addRemoveUsersRoles(
            $beforeArrays, 
            $afterArrays, 
            $constraints 
            ) {
        
        foreach( $constraints as $constraint ) {
            $this->getLogger()->info( '**** update users roles for constraint: ' . $constraint->getId() );
            $constraintId = $constraint->getid();
            $beforeArray = array();
            $afterArray = array();
            
            // we check if we have any users for each constraint
            // before...
            if ( array_key_exists ( $constraintId, $beforeArrays ) ) {
                $beforeArray = $beforeArrays[$constraint->getid()];       
            }
            
            // ... and after
            if ( array_key_exists ( $constraint->getid(), $afterArrays ) ) {
               $afterArray = $afterArrays[$constraint->getid()];
            }
            
            if ( ! $beforeArray && ! $afterArray ) {
                // no matched user neither before nor after, nothing to do
                $this->getLogger()->info( 'no users either before or after in constraint ID ' . $constraintId );
                return;
                
            } elseif ( ! $beforeArray &&  $afterArray ) { 
                // nothing before but there is after : add all users
                $this->getLogger()->info( 'nothing before but there is after : add role all users in constraint ID ' . $constraintId );
                foreach ( $afterArray as $user ) {
                    $this->updateUserRole( $user, $constraint, 'add' );
                }
            } elseif ( $beforeArray &&   ! $afterArray ) {
                // nothing after but there was before : remove all users
               $this->getLogger()->info( 'nothing after but there was before : remove role for all users in constraint ' . $constraintId );
                foreach ( $beforeArray as $user ) {
                    $this->updateUserRole( $user, $constraint, 'remove' );
                }
            } elseif ( $beforeArray && $afterArray ) {
                // data in both arrays
                $this->getLogger()->info( 'Comparing users in constraint ' . $constraintId );
                
                foreach ( $beforeArray as $beforeUser ) {
                    if (array_key_exists($beforeUser->getId(), $afterArray )) {
                        // exists in both
                        $this->getLogger()->info( 'user: ' . $beforeUser->getId() . ' exists before and after, move to next' );
                        // remove to prevent further comparaison
                        unset( $afterArray[$beforeUser->getId()] );
                    } else {
                        // existed before but no more, so remove
                        $this->updateUserRole( $beforeUser, $constraint, 'remove' );
                    }
                }
                
                foreach ( $afterArray as $afterUser ) {
                    if ( array_key_exists($afterUser->getId(), $beforeArray )) {
                        // exists in both
                        $this->getLogger()->info( 'user exists after and was before ' . $afterUser->getId() );
                    } else {
                        // new user
                        $this->updateUserRole( $afterUser, $constraint, 'add' );
                    }
                }
            }
        }        
    }
    
    private function getLogger() {
        return $this->container->get('logger');
    }
    
    private function updateUserRole( $user, $constraint, $action ) {
        $roleManager = $this->container->get('claroline.manager.role_manager');
        $em = $this->getDoctrine()->getManager();
        $userRoles = $user->getEntityRoles();
        $userMoocSessions = $user->getMoocSessions();  
        
        // For all moocs of the constraints, update user if he's not admin or creator
        foreach ( $constraint->getMoocs() as $mooc ) {
            
            // Do not remove user subscribed to session
            if ( $action == 'remove' ) {
                $moocSessions = $mooc->getMoocSessions();
                foreach ( $moocSessions as $moocSession ) {
                    if ( $moocSession->getUsers()->contains( $user ) ) {
                        $this->getLogger()->info('The user ID: ' . $user->getId() . ' cannot be removed because he is already subscribed this mooc session' );
                        continue 2;
                    }
                }
            }
            
            $workspace = $mooc->getWorkspace();

            if ( ! $userRoles->contains($roleManager->getManagerRole($workspace)) &&
                 ! $userRoles->contains($roleManager->getRoleByName('ROLE_ADMIN'))) {
                
                $role = $roleManager->getCollaboratorRole($workspace);
                
                switch ( $action ) {
                    
                case 'add':
                    // if not already collaborator, add role
                    if ( ! $userRoles->contains($role) ) {
                        $this->getLogger()->info('adding role: ' . $role->getId() . ' to user: ' . $user->getId() );
                        $user->addRole($role);
                        $em->persist($user);
                    }
                    break;
                    
                case 'remove':
                    // if already collaborator, remove role
                    if ( $userRoles->contains($role) ) {
                        $this->getLogger()->info('remove role: ' . $role->getId() . ' to user: ' . $user->getId() );
                        $user->removeRole($role);
                        $em->persist($user);
                    }
                    break;  
                }
            }
        }
        // write database
        $em->flush();
    }
}
