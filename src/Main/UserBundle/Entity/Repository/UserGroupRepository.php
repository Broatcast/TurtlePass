<?php

namespace Main\UserBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Main\UserBundle\Entity\UserGroup;

class UserGroupRepository extends EntityRepository
{
    /**
     * @param UserGroup $entity
     * @param bool      $flush
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(UserGroup $entity, $flush = true)
    {
        $this->getEntityManager()->persist($entity);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param UserGroup $entity
     * @param bool      $flush
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function remove(UserGroup $entity, $flush = true)
    {
        $this->getEntityManager()->remove($entity);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return QueryBuilder
     */
    public function qbAll()
    {
        return $this->createQueryBuilder('user_group');
    }
}
