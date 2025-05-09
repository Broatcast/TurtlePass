<?php

namespace Main\UserBundle\Entity\Repository;

use Doctrine\ORM\QueryBuilder;
use UniqueLibs\FOSUserRecaptchaProtectionBundle\Entity\LoginAccessRepository as BaseRepository;

class LoginAccessRepository extends BaseRepository
{
    /**
     * @return QueryBuilder
     */
    public function qbAll()
    {
        return $this->createQueryBuilder('login_access');
    }
}
