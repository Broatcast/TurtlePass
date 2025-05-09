<?php

namespace Main\PasswordBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Main\UserBundle\Entity\User;
use Main\UserBundle\Entity\UserGroup;

class PasswordGroupUserGroupAccessRepository extends EntityRepository
{
    /**
     * @param PasswordGroupUserGroupAccess $entity
     * @param bool                         $flush
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(PasswordGroupUserGroupAccess $entity, $flush = true)
    {
        $this->getEntityManager()->persist($entity);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param PasswordGroupUserGroupAccess $entity
     * @param bool                         $flush
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function remove(PasswordGroupUserGroupAccess $entity, $flush = true)
    {
        $this->getEntityManager()->remove($entity);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param UserGroup                    $userGroup
     * @param User $user
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function qbAllByUserGroup(UserGroup $userGroup, User $user)
    {
        $qb = $this->createQueryBuilder('password_group_user_group_access')
            ->select('password_group_user_group_access, password_group, password_group_parent, password_group_sorting')
            ->innerJoin('password_group_user_group_access.passwordGroup', 'password_group')
            ->leftJoin('password_group.parent', 'password_group_parent')
            ->leftJoin('password_group.passwordGroupSorting', 'password_group_sorting', 'WITH', 'password_group_sorting.user = :pUserId')
            ->andWhere('password_group_user_group_access.userGroup = :pUserGroupId')
            ->setParameter('pUserGroupId', $userGroup->getId())
            ->setParameter('pUserId', $user->getId());

        return $qb;
    }

    /**
     * @param PasswordGroup $passwordGroup
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function qbAllByPasswordGroup(PasswordGroup $passwordGroup)
    {
        return $this->createQueryBuilder('password_group_user_group_access')
            ->select('password_group_user_group_access, user_group')
            ->innerJoin('password_group_user_group_access.userGroup', 'user_group')
            ->andWhere('password_group_user_group_access.passwordGroup = :pPasswordGroupId')
            ->setParameter('pPasswordGroupId', $passwordGroup->getId());
    }

    /**
     * @param UserGroup $userGroup
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function qbDeleteAllByUserGroup(UserGroup $userGroup)
    {
        return $this->createQueryBuilder('password_group_user_group_access')
            ->delete()
            ->where('password_group_user_group_access.user = :pUserGroupId')
            ->setParameter('pUserGroupId', $userGroup->getId());
    }

    /**
     * @param UserGroup $userGroup
     *
     * @return bool
     */
    public function hasUserGroupAnyAccess(UserGroup $userGroup)
    {
        try {
            return (int)$this->createQueryBuilder('password_group_user_group_access')
                    ->select('count(password_group_user_group_access.id)')
                    ->andWhere('password_group_user_group_access.userGroup = :pUserGroupId')
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
