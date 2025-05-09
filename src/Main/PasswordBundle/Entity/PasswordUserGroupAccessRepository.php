<?php

namespace Main\PasswordBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Main\UserBundle\Entity\User;
use Main\UserBundle\Entity\UserGroup;

class PasswordUserGroupAccessRepository extends EntityRepository
{
    /**
     * @param PasswordUserGroupAccess $entity
     * @param bool                    $flush
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(PasswordUserGroupAccess $entity, $flush = true)
    {
        $this->getEntityManager()->persist($entity);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param PasswordUserGroupAccess $entity
     * @param bool                    $flush
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function remove(PasswordUserGroupAccess $entity, $flush = true)
    {
        $this->getEntityManager()->remove($entity);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param UserGroup $userGroup
     * @param User      $user
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function qbAllByUserGroup(UserGroup $userGroup, User $user)
    {
        return $this->createQueryBuilder('password_user_group_access')
            ->select('password_user_group_access, password, password_group, password_group_parent, password_group_sorting')
            ->innerJoin('password_user_group_access.password', 'password')
            ->innerJoin('password.passwordGroup', 'password_group')
            ->leftJoin('password_group.parent', 'password_group_parent')
            ->leftJoin('password_group.passwordGroupSorting', 'password_group_sorting', 'WITH', 'password_group_sorting.user = :pUserId')
            ->andWhere('password_user_group_access.userGroup = :pUserGroupId')
            ->setParameter('pUserGroupId', $userGroup->getId())
            ->setParameter('pUserId', $user->getId())
            ;
    }

    /**
     * @param Password $password
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function qbAllByPassword(Password $password)
    {
        return $this->createQueryBuilder('password_user_group_access')
            ->select('password_user_group_access, user_group')
            ->innerJoin('password_user_group_access.userGroup', 'user_group')
            ->andWhere('password_user_group_access.password = :pPasswordId')
            ->setParameter('pPasswordId', $password->getId());
    }

    /**
     * @param UserGroup $userGroup
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function qbDeleteAllByUserGroup(UserGroup $userGroup)
    {
        return $this->createQueryBuilder('password_user_group_access')
            ->delete()
            ->where('password_user_group_access.userGroup = :pUserGroupId')
            ->setParameter('pUserGroupId', $userGroup->getId());
    }

    /**
     * @param PasswordGroup $passwordGroup
     * @param array         $userGroupIds
     *
     * @return bool
     */
    public function hasPasswordUserGroupAccessByPasswordGroupAndUser(PasswordGroup $passwordGroup, array $userGroupIds)
    {
        try {
            return (int)$this->createQueryBuilder('password_user_group_access')
                    ->select('count(password_user_group_access.id)')
                    ->innerJoin('password_user_group_access.password', 'password')
                    ->innerJoin('password.passwordGroup', 'password_group')
                    ->andWhere('password_group.id = :pPasswordGroupId')
                    ->andWhere('password_user_group_access.userGroup IN (:pUserGroupIds)')
                    ->setMaxResults(1)
                    ->setParameter('pUserGroupIds', $userGroupIds)
                    ->setParameter('pPasswordGroupId', $passwordGroup->getId())
                    ->getQuery()->getSingleScalarResult() > 0;
        } catch (NonUniqueResultException $exception) {
            throw new \LogicException('Not possible.', 0 , $exception);
        } catch (NoResultException $exception) {
            throw new \LogicException('Not possible.', 0 , $exception);
        }
    }

    /**
     * @param UserGroup $userGroup
     *
     * @return bool
     */
    public function hasUserGroupAnyAccess(UserGroup $userGroup)
    {
        try {
            return (int)$this->createQueryBuilder('password_user_group_access')
                    ->select('count(password_user_group_access.id)')
                    ->andWhere('password_user_group_access.userGroup = :pUserGroupId')
                    ->setMaxResults(1)
                    ->setParameter('pUserGroupId', $userGroup->getId())
                    ->getQuery()->getSingleScalarResult() > 0;
        } catch (NonUniqueResultException $exception) {
            throw new \LogicException('Not possible.', 0 , $exception);
        } catch (NoResultException $exception) {
            throw new \LogicException('Not possible.', 0 , $exception);
        }
    }
}
