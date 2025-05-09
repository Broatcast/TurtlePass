<?php

namespace Main\PasswordBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Main\PasswordBundle\Entity\PasswordType\BankAccountPassword;
use Main\UserBundle\Entity\User;

class PasswordRepository extends EntityRepository
{
    /**
     * @param Password $entity
     * @param bool     $flush
     */
    public function save(Password $entity, $flush = true)
    {
        $this->getEntityManager()->persist($entity);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param Password $entity
     * @param bool     $flush
     */
    public function remove(Password $entity, $flush = true)
    {
        $this->getEntityManager()->remove($entity);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param int  $passwordId
     * @param User $user
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function qbPasswordByIdAndUser($passwordId, User $user)
    {
        $userGroupIds = [];

        foreach ($user->getUserGroups() as $userGroup) {
            $userGroupIds[] = $userGroup->getId();
        }

        $qb = $this->createQueryBuilder('password');
        $qb
            ->select('password, password_accesses, password_user_group_accesses')
            ->leftJoin('password.passwordAccesses', 'password_accesses', 'WITH', 'password_accesses.user = :pUserId')
            ->leftJoin('password.passwordUserGroupAccesses', 'password_user_group_accesses', 'WITH', 'password_user_group_accesses.userGroup IN (:pUserGroupIds)')
            ->andWhere('password.id = :pPassword')
            ->setParameter('pPassword', $passwordId)
            ->setParameter('pUserId', $user->getId())
            ->setParameter('pUserGroupIds', $userGroupIds);

        return $qb;
    }

    /**
     * @param PasswordGroup $passwordGroup
     * @param User          $user
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function qbAllByPasswordGroupAndUser(PasswordGroup $passwordGroup, User $user)
    {
        $userGroupIds = [];

        foreach ($user->getUserGroups() as $userGroup) {
            $userGroupIds[] = $userGroup->getId();
        }

        $qb = $this->createQueryBuilder('password');
        $qb
            ->select('password, password_group, password_accesses, password_group_accesses, password_user_group_accesses, password_group_user_group_accesses')
            ->innerJoin('password.passwordGroup', 'password_group')
            ->leftJoin('password_group.passwordGroupAccesses', 'password_group_accesses', 'WITH', 'password_group_accesses.user = :pUserId')
            ->leftJoin('password_group.passwordGroupUserGroupAccess', 'password_group_user_group_accesses', 'WITH', 'password_group_user_group_accesses.userGroup IN(:pUserGroupIds)')
            ->leftJoin('password.passwordAccesses', 'password_accesses', 'WITH', 'password_accesses.user = :pUserId')
            ->leftJoin('password.passwordUserGroupAccesses', 'password_user_group_accesses', 'WITH', 'password_user_group_accesses.userGroup IN(:pUserGroupIds)')
            ->andWhere('password.passwordGroup = :pPasswordGroup')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->eq('password_accesses.user', ':pUserId'),
                    $qb->expr()->eq('password_group_accesses.user', ':pUserId'),
                    $qb->expr()->in('password_group_user_group_accesses.userGroup', ':pUserGroupIds'),
                    $qb->expr()->in('password_user_group_accesses.userGroup', ':pUserGroupIds')
                )
            )
            ->setParameter('pPasswordGroup', $passwordGroup->getId())
            ->setParameter('pUserId', $user->getId())
            ->setParameter('pUserGroupIds', $userGroupIds);

        return $qb;
    }

    /**
     * @param $offset
     * @param $limit
     *
     * @return array
     */
    public function getAllByPasswords($offset, $limit)
    {
        $qb = $this->createQueryBuilder('password');

        $qb
            ->select('password')
            ->innerJoin('password.passwordGroup', 'password_group')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
        ;

        return $qb
            ->getQuery()
            ->getScalarResult();
    }

    /**
     * @param User   $user
     * @param string $query
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function qbSearchByUserAndQuery(User $user, $query)
    {
        $userGroupIds = [];

        foreach ($user->getUserGroups() as $userGroup) {
            $userGroupIds[] = $userGroup->getId();
        }

        $qb = $this->createQueryBuilder('password');
        $qb
            ->select('password, password_group, password_accesses, password_group_accesses, password_user_group_accesses, password_group_user_group_accesses')
            ->innerJoin('password.passwordGroup', 'password_group')
            ->leftJoin('password_group.passwordGroupAccesses', 'password_group_accesses', 'WITH', 'password_group_accesses.user = :pUserId')
            ->leftJoin('password_group.passwordGroupUserGroupAccess', 'password_group_user_group_accesses', 'WITH', 'password_group_user_group_accesses.userGroup IN(:pUserGroupIds)')
            ->leftJoin('password.passwordAccesses', 'password_accesses', 'WITH', 'password_accesses.user = :pUserId')
            ->leftJoin('password.passwordUserGroupAccesses', 'password_user_group_accesses', 'WITH', 'password_user_group_accesses.userGroup IN(:pUserGroupIds)')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->eq('password_accesses.user', ':pUserId'),
                    $qb->expr()->eq('password_group_accesses.user', ':pUserId'),
                    $qb->expr()->in('password_group_user_group_accesses.userGroup', ':pUserGroupIds'),
                    $qb->expr()->in('password_user_group_accesses.userGroup', ':pUserGroupIds')
                )
            )
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('password.nameCanonical', ':pQuery'),
                    $qb->expr()->like('password.usernameCanonical', ':pQuery'),
                    $qb->expr()->like('password.url', ':pQuery')
                )
            )
            ->setParameter('pQuery', '%' . strtolower($query) . '%')
            ->setParameter('pUserId', $user->getId())
            ->setParameter('pUserGroupIds', $userGroupIds)
            ->setMaxResults(10);

        return $qb;
    }
}
