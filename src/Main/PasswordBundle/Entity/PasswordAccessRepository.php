<?php

namespace Main\PasswordBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Main\UserBundle\Entity\User;

class PasswordAccessRepository extends EntityRepository
{
    /**
     * @param PasswordAccess $entity
     * @param bool           $flush
     */
    public function save(PasswordAccess $entity, $flush = true)
    {
        $this->getEntityManager()->persist($entity);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param PasswordAccess $entity
     * @param bool           $flush
     */
    public function remove(PasswordAccess $entity, $flush = true)
    {
        $this->getEntityManager()->remove($entity);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param User $user
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function qbAllByUser(User $user)
    {
        return $this->createQueryBuilder('password_access')
            ->select('password_access, password, password_group, password_group_parent, password_group_sorting')
            ->innerJoin('password_access.password', 'password')
            ->innerJoin('password.passwordGroup', 'password_group')
            ->leftJoin('password_group.passwordGroupSorting', 'password_group_sorting', 'WITH', 'password_group_sorting.user = :pUserId')
            ->leftJoin('password_group.parent', 'password_group_parent')
            ->andWhere('password_access.user = :pUserId')
            ->setParameter('pUserId', $user->getId());
    }

    /**
     * @param Password $password
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function qbAllByPassword(Password $password)
    {
        return $this->createQueryBuilder('password_access')
            ->select('password_access, user')
            ->innerJoin('password_access.user', 'user')
            ->andWhere('password_access.password = :pPasswordId')
            ->setParameter('pPasswordId', $password->getId());
    }

    /**
     * @param User $user
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function qbDeleteAllByUser(User $user)
    {
        return $this->createQueryBuilder('password_access')
            ->delete()
            ->where('password_access.user = :pUserId')
            ->setParameter('pUserId', $user->getId());
    }

    /**
     * @param PasswordGroup $passwordGroup
     * @param User          $user
     *
     * @return bool
     */
    public function hasPasswordAccessByPasswordGroupAndUser(PasswordGroup $passwordGroup, User $user)
    {
        return (int)$this->createQueryBuilder('password_access')
            ->select('count(password_access.id)')
            ->innerJoin('password_access.password', 'password')
            ->innerJoin('password.passwordGroup', 'password_group')
            ->andWhere('password_group.id = :pPasswordGroupId')
            ->andWhere('password_access.user = :pUserId')
            ->setMaxResults(1)
            ->setParameter('pUserId', $user->getId())
            ->setParameter('pPasswordGroupId', $passwordGroup->getId())
            ->getQuery()->getSingleScalarResult() > 0;
    }
}
