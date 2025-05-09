<?php

namespace Main\UserBundle\Ldap;

use FOS\UserBundle\Model\UserManagerInterface;
use FR3D\LdapBundle\Hydrator\HydratorInterface;
use Main\LanguageBundle\Services\LanguageManager;
use Main\UserBundle\Entity\LdapUser;
use Symfony\Component\Security\Core\User\UserInterface;

class UserHydrator implements HydratorInterface
{
    /**
     * @var LanguageManager
     */
    protected $languageManager;

    /**
     * @var UserManagerInterface
     */
    protected $userManager;

    /**
     * @param LanguageManager      $languageManager
     * @param UserManagerInterface $userManager
     */
    public function __construct(LanguageManager $languageManager, UserManagerInterface $userManager)
    {
        $this->languageManager = $languageManager;
        $this->userManager = $userManager;
    }

    /**
     * Populate an user with the data retrieved from LDAP.
     *
     * @param array $ldapEntry LDAP result information as a multi-dimensional array.
     *              see {@link http://www.php.net/function.ldap-get-entries.php} for array format examples.
     *
     * @return UserInterface
     */
    public function hydrate(array $ldapEntry)
    {
        $username = null;
        if (array_key_exists('uid', $ldapEntry)) {
            $username = $ldapEntry['uid'][0];
        } else if (array_key_exists('userprincipalname', $ldapEntry)) {
            $username = $ldapEntry['userprincipalname'][0];
        }

        $existingUser = $this->userManager->findAllUserByUsername($username);
        if ($existingUser instanceof UserInterface) {
            return $existingUser;
        }

        $plainPassword = null;
        if (array_key_exists('userpassword', $ldapEntry)) {
            $plainPassword = $ldapEntry['userpassword'][0];
        } else {
            $plainPassword = '0';
        }

        $user = new LdapUser();
        $user->setUsername($username);
        $user->setPlainPassword($plainPassword);
        $user->setDn($ldapEntry['dn']);
        $user->setLanguage($this->languageManager->getDefaultLanguage());

        return $user;
    }
}