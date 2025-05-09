<?php

namespace Main\PasswordBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Main\UserBundle\Entity\User;

class PasswordGroupAccessRepository extends EntityRepository
{
    /**
     * @param PasswordGroupAccess $entity
     * @param bool                $flush
     */
    public function save(PasswordGroupAccess $entity, $flush = true)
    {
        $this->getEntityManager()->persist($entity);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param PasswordGroupAccess $entity
     * @param bool                $flush
     */
    public function remove(PasswordGroupAccess $entity, $flush = true)
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
        $qb = $this->createQueryBuilder('password_group_access')
            ->select('password_group_access, password_group, password_group_parent, password_group_sorting')
            ->innerJoin('password_group_access.passwordGroup', 'password_group')
            ->leftJoin('password_group.parent', 'password_group_parent')
            ->leftJoin('password_group.passwordGroupSorting', 'password_group_sorting', 'WITH', 'password_group_sorting.user = :pUserId')
            ->andWhere('password_group_access.user = :pUserId')
            ->setParameter('pUserId', $user->getId())
            ->orderBy('password_group_sorting.sorting', 'ASC');

        return $qb;
    }

    /**
     * @param PasswordGroup $passwordGroup
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function qbAllByPasswordGroup(PasswordGroup $passwordGroup)
    {
        return $this->createQueryBuilder('password_group_access')
            ->select('password_group_access, user')
            ->innerJoin('password_group_access.user', 'user')
            ->andWhere('password_group_access.passwordGroup = :pPasswordGroupId')
            ->setParameter('pPasswordGroupId', $passwordGroup->getId());
    }

    /**
     * @param User $user
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function qbDeleteAllByUser(User $user)
    {
        return $this->createQueryBuilder('password_group_access')
            ->delete()
            ->where('password_group_access.user = :pUserId')
            ->setParameter('pUserId', $user->getId());
    }
}
