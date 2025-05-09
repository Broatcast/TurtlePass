<?php

namespace Main\PasswordBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Main\UserBundle\Entity\User;

class PasswordGroupRepository extends EntityRepository
{
    /**
     * @param PasswordGroup $entity
     * @param bool          $flush
     */
    public function save(PasswordGroup $entity, $flush = true)
    {
        $this->getEntityManager()->persist($entity);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param PasswordGroup $entity
     * @param bool          $flush
     */
    public function remove(PasswordGroup $entity, $flush = true)
    {
        $this->getEntityManager()->remove($entity);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param int  $passwordGroupId
     * @param User $user
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function qbPasswordGroupByIdAndUser($passwordGroupId, User $user)
    {
        $qb = $this->createQueryBuilder('password_group');
        $qb
            ->select('password_group, password_group_accesses')
            ->leftJoin('password_group.passwordGroupAccesses', 'password_group_accesses', 'WITH', 'password_group_accesses.user = :pUserId')
            ->andWhere('password_group.id = :pPasswordGroup')
            ->setParameter('pPasswordGroup', $passwordGroupId)
            ->setParameter('pUserId', $user->getId());

        return $qb;
    }

    /**
     * @param PasswordGroup $passwordGroup
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function qbByParent(PasswordGroup $passwordGroup)
    {
        $qb = $this->createQueryBuilder('password_group');
        $qb
            ->andWhere('password_group.parent = :pPasswordGroup')
            ->setParameter('pPasswordGroup', $passwordGroup->getId());

        return $qb;
    }

    /**
     * @param PasswordGroup $passwordGroup
     *
     * @return int
     */
    public function countByParent(PasswordGroup $passwordGroup)
    {
        return (int) $this->qbByParent($passwordGroup)->select('count(password_group)')->getQuery()->getSingleScalarResult();
    }
}
