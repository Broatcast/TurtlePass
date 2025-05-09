<?php

namespace Main\AppBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController as OriginalFOSRestController;
use FOS\RestBundle\Context\Context;
use Main\UserBundle\Entity\User;

class FOSRestController extends OriginalFOSRestController
{
    /**
     * @return null|User
     */
    public function getUser()
    {
        return parent::getUser();
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->container->get('request_stack')->getCurrentRequest()->getLocale();
    }

    /**
     * @param User  $user
     * @param array $additionalGroups
     *
     * @return Context
     */
    public function getContextByUser(User $user, array $additionalGroups = [])
    {
        $groups = ['Default'];

        foreach ($user->getRoles() as $userRole) {
            $groups[] = strtoupper($userRole);

            if (strtoupper($userRole) == 'ROLE_SUPER_ADMIN') {
                if (!in_array('ROLE_ADMIN', $groups)) {
                    $groups[] = 'ROLE_ADMIN';
                }
            }
        }

        $groups = array_merge($groups, $additionalGroups);

        $context = new Context();
        $context->setGroups($groups);

        return $context;
    }
}
