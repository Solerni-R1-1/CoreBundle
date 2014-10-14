<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Translation\TranslatorInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Doctrine\ORM\EntityManager;

/**
 * @DI\Validator("csv_user_validator")
 */
class CsvUserValidator extends ConstraintValidator
{
    private $validator;
    private $translator;
    private $em;

    /**
     * @DI\InjectParams({
     *     "validator" = @DI\Inject("validator"),
     *     "trans"     = @DI\Inject("translator"),
     *     "em"		   = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct(ValidatorInterface $validator, TranslatorInterface $translator, EntityManager $em)
    {
        $this->validator = $validator;
        $this->translator = $translator;
        $this->em = $em;
    }

    public function validate($value, Constraint $constraint)
    {
       // $lines = str_getcsv(file_get_contents($value), PHP_EOL);

        /*foreach ($lines as $line) {
            $linesTab = explode(';', $line);
            $nbElements = count($linesTab);

            if ($nbElements < 5) {
                $this->context->addViolation($constraint->message);

                return;
            }
        }*/

//         $usernames = array();
//         $mails = array();

//         foreach ($lines as $i => $line) {
//             $user = explode(';', $line);
//             $firstName = $user[0];
//             $lastName = $user[1];
//             $username = $user[2];
//             $pwd = $user[3];
//             $email = $user[4];
//             $code = isset($user[5])? $user[5] : null;
//             $phone = isset($user[6])? $user[6] : null;

//             (!array_key_exists($email, $mails)) ?
//                 $mails[$email] = array($i + 1):
//                 $mails[$email][] = $i + 1;
//             (!array_key_exists($username, $usernames)) ?
//                 $usernames[$username] = array($i + 1):
//                 $usernames[$username][] = $i + 1;

//             $newUser = new User();
//             $newUser->setFirstName($firstName);
//             $newUser->setLastName($lastName);
//             $newUser->setUsername($username);
//             $newUser->setPlainPassword($pwd);
//             $newUser->setMail($email);
//             $newUser->setAdministrativeCode($code);
//             $newUser->setPhone($phone);
//         }
        
//         foreach ($usernames as $username => $lines) {
//             if (count($lines) > 1) {
//                 $msg = $this->translator->trans(
//                     'username_found_at',
//                     array('%username%' => $username, '%lines%' => $this->getLines($lines)),
//                     'platform'
//                 ) . ' ';

//                 $this->context->addViolation($msg);
//             }
//         }

//         foreach ($mails as $mail => $lines) {
//             if (count($lines) > 1) {
//                 $msg = $this->translator->trans(
//                     'email_found_at',
//                     array('%email%' => $mail, '%lines%' => $this->getLines($lines)),
//                     'platform'
//                 ) . ' ';
//                 $this->context->addViolation($msg);
//             }
//         }
        
//         // Validate users
//         $repo = $this->em->getRepository("ClarolineCoreBundle:User");
        
//         $alreadyExistingUsernames = $repo->findByUsernameIn(array_keys($usernames));
//         $alreadyExistingMails = $repo->findByMailIn(array_keys($mails));

//         foreach ($alreadyExistingUsernames as $user) {
//         	$msg = $this->translator->trans(
//         			'username_already_exists',
//         			array('%username%' => $user->getUsername(), '%lines%' => $this->getLines($usernames[$user->getUsername()])),
//         			'platform'
//         	) . ' ';
        	 
//         	$this->context->addViolation($msg);
//         }

//         foreach ($alreadyExistingMails as $user) {
//            $msg = $this->translator->trans(
//                 'mail_already_exists',
//                 array('%email%' => $user->getMail(), '%lines%' => $this->getLines($usernames[$user->getUsername()])),
// 				'platform'
//            ) . ' ';
//            $this->context->addViolation($msg);
//         }
    }

    private function getLines($lines)
    {
        $countLines = count($lines);
        $l = '';

        foreach ($lines as $i => $line) {
            $l .= $line;

            if ($i < $countLines - 1) {
                $l .= ', ';
            }
        }

        return $l;
    }
}
