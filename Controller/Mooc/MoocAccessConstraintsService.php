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
use Doctrine\ORM\EntityManager;


/**
 * Mooc\MoocAccessConstraints service.
 */
class MoocAccessConstraintsService extends Controller
{
    
    protected $em;
    protected $constraintsRepo;
    protected $sessionsByUsersRepo;
    protected $userRepo;


    public function __construct( EntityManager $em ) {
        $this->em = $em;
        $this->constraintsRepo = $this->em->getRepository('ClarolineCoreBundle:Mooc\MoocAccessConstraints');
        $this->sessionsByUsersRepo = $this->em->getRepository('ClarolineCoreBundle:Mooc\SessionsByUsers');
        $this->userRepo = $this->em->getRepository('ClarolineCoreBundle:User');
        
        // No log for performance issue
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);

    }
    
    private function convert($size) {
        $unit=array('b','kb','mb','gb','tb','pb');
        return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
    }
    
    private function echo_memory_usage( $context_message = '' ) {

        $this->getLogger()->info($context_message);
        $this->getLogger()->info('Memory use: ' . $this->convert( memory_get_usage() ) );
        $this->getLogger()->info('Memory peak: ' . $this->convert( memory_get_peak_usage() ) );
       
    }
           
    private function getLogger() {
        return $this->container->get('logger');
    }
    
    /*
     * This function add a new user to the constraint
     */
	public function processUpgradeUsers(array $users) {
        
        foreach( $users as $user ) {
            $this->echo_memory_usage('Adding user' . $user->getUsername());
        }
        

        $constraintsRepository = $this->em->getRepository('ClarolineCoreBundle:Mooc\MoocAccessConstraints');
    	
    	foreach ($users as $user) {
    		/* @var $user User */
        	$constraints = $constraintsRepository->findByUserMail( $user->getMail() );

    		foreach ($constraints as $constraint) {
    			foreach ($constraint->getMoocs() as $mooc) {
    				foreach ($mooc->getMoocSessions() as $session) {
			    		$newSession = new SessionsByUsers();
			    		$newSession->setMoocAccessConstraints($constraint);
			    		$newSession->setUser($user);
			    		$newSession->setMoocSession($session);
			    		$newSession->setMoocOwner($constraint->getMoocOwner());
			    		$this->em->persist($newSession);
    				}
    			}
    		}
            
    		$this->em->merge($user);
    		$this->em->flush();
    	}
    }
    
    /*
     * Function lauched when a constraint is updated
     * This function will removed others that are not in the autorized list
     * then add users that are in
     */
    public function processUpgradeConstraints(array $constraints) {
               
        // Do not log for performance issue
        //$this->em->getConnection()->getConfiguration()->setSQLLogger(null);
        
        //$this->echo_memory_usage('first check');
        
    	foreach ( $constraints as $constraint ) {
    		/* @var $constraint MoocAccessConstraints */
            
            $this->processUpgradeConstraint( $constraint );

            // Update constraint and end transaction
            $this->em->flush();
            $this->em->clear();
    	}
    }
    /*
     * Update a constraint user list 
     */
    public function processUpgradeConstraint( $constraint, $mooc = null ) {

        $usersArrayList = $this->getUsersListMatchingConstraint( $constraint );
        // Reduce useless array depth
        $usersList = array();
        foreach ( $usersArrayList as $userArray ) {
           $usersList[] = $userArray['id'];
        }

        // First, remove from DB the rows where users ID are not in the list. They were removed
        $this->checkAndRemoveUsersForConstraint( $constraint, $usersList );

        // Second step, get users already auitorized
        $usersScalarResult = $this->sessionsByUsersRepo->getListofUsersAlreadyPresent( $constraint, $usersList, $mooc );
        $usersInRows = array();
        foreach ( $usersScalarResult as $userArray ) {
           $usersInRows[] = $userArray['user'];
        }
        // Then remove them from the list 
        $usersList = array_diff( $usersList, $usersInRows );

        if ( count ( $usersList ) > 0 ) {
            // Add what is left of users to the DB
            $this->addUsersToAllowedTable( $constraint, $usersList );
        }
        
    }
    
    public function processRemoveConstraint( $constraint, $mooc ) {
        
        foreach( $mooc->getMoocSessions() as $session ) {
            $objectstoDelete = $this->sessionsByUsersRepo->findBy(array( 'moocSession' => $session, 'moocAccessConstraints' => $constraint ));
            if ( $objectstoDelete ) {
                foreach( $objectstoDelete as $sessionByUser ) {
                    $rowstoDelete[] = $sessionByUser->getId();
                }
                $this->sessionsByUsersRepo->deleteRowsFromIds( $rowstoDelete );
            }
        }
     }
    
    /*
     * return an array empty or list or users (by getScalarResults() from repository)
     */
    function getUsersListMatchingConstraint( $constraint ) {
        
        $userRepository = $this->em->getRepository('ClarolineCoreBundle:User');
        
        $usersArrayList = array();
        $whiteListArray = array();
        $patternsArray = array();
        
        // extract constraint info
        if ($constraint->getWhitelist() != "") {
            $whiteListArray = explode("\r\n", $constraint->getWhitelist());
        } 
        
        if ($constraint->getPatterns() != "") {
            $patternsArray = explode("\r\n", $constraint->getPatterns());
        } 

        // Get users ID list matching the constraint
        if (count($patternsArray) > 0 || count($whiteListArray) > 0) {
            $usersArrayList = $userRepository->findByMailInOrLike($whiteListArray, $patternsArray);
        }
        
        return $usersArrayList;
    }
    
    /*
     * This function fetches rows where users was presents in the constraints but are now removed
     * It also launch the removal of collabator roles from unsubscribed users
     */
    public function checkAndRemoveUsersForConstraint( MoocAccessConstraints $constraint, $usersList = array() ) {
        
        // Get all rows from sessionsByUsers that have users not in the list for this constraint
        $rowsDataSet = $this->sessionsByUsersRepo->getConstraintRowsNotMatchUsersList( $constraint->getId(), $usersList );
        
        if ( $rowsDataSet ) {
            // Create arrays
            foreach ( $rowsDataSet as $dataSet ) {
                $rowstoDelete[] = $dataSet['id'];
                $usersToDelete[] = $dataSet['user'];
            }
            
            //Remove sessions by users rows for DB (there is a function in repo to remove entities via dql)
            $this->sessionsByUsersRepo->deleteRowsFromIds( $rowstoDelete );

            $this->em->flush();
            $this->em->clear();
        }
      
    }
    
    /*
     * Returns array of sessions for a constraint
     */
    public function getMoocsFromConstraints( MoocAccessConstraints $constraint ) {
        
        $sessions = array();
        
        if ( $constraint->getMoocs() ) {
	    	foreach ($constraint->getMoocs() as $mooc) {
	    		foreach ($mooc->getMoocSessions() as $session) {
	    			$sessions[] = $session;
	    		}
	    	}
    	}
        
        return $sessions;
    }
    
    /*
     *  Add a array of users ids to SessionsByUsers for a constraint
     */
    public function addUsersToAllowedTable( MoocAccessConstraints $constraint, $usersList = array() ) {
        
        $moocOwner = $constraint->getMoocOwner();
        
        foreach ( $this->getMoocsFromConstraints( $constraint ) as $session ) {
           
            foreach ( $usersList as $key => $userId ) {
                $user = $this->userRepo->findOneBy( array( 'id' => $userId ) );
                
                // If already in database, dont add a new one
                if ( $this->sessionsByUsersRepo->findOneBy( array( 'user' => $user, 'moocSession' => $session, 'moocAccessConstraints' => $constraint ) ) ) {
                    continue;
                }
                
                // Add new entry
                $newSession = new SessionsByUsers();
                $newSession->setMoocAccessConstraints($constraint);
	    		$newSession->setUser($user);
	    		$newSession->setMoocSession($session);
	    		$newSession->setMoocOwner($moocOwner);
                $this->em->merge($newSession);
                
                // Loops
                if ( $key % 500 === 0 ) {
                    $this->em->flush();
                    $this->em->clear();
                }

            }
        }
        
        $this->em->flush();
        $this->em->clear();
    }
    
  
    public function processDelete(array $constraints, User $user = null)
    {
    	$logger = $this->getLogger();
       
        $sessionsByUsersRepository = $this->em->getRepository('ClarolineCoreBundle:Mooc\SessionsByUsers');

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
   
    public function refreshDeletedConstraintForWorkspace(AbstractWorkspace $workspace) {
    	if ($workspace->getMooc() != null) {
    		
    		/* @var $constraintsRepo MoocAccessConstraintsRepository */
    		$constraintsRepo = $this->em->getRepository('ClarolineCoreBundle:Mooc\MoocAccessConstraints');
    		$constraints = $constraintsRepo->findByMooc($workspace->getMooc(), $workspace->getMooc()->getAccessConstraints()->toArray());
            
    		foreach ($constraints as $constraint) {
    			foreach ($constraint->getSessionsByUsers() as $sessionByUser) {
    				$moocSession = $sessionByUser->getMoocSession();
    				if ($workspace->getMooc()->getMoocSessions()->contains($moocSession)) {
	    				$this->em->remove($sessionByUser);
    				}
    			}
    		}
            $this->em->flush();
            $this->em-clear();
    	}
    }
}
