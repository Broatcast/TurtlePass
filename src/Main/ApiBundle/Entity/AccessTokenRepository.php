<?php

namespace Main\ApiBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Main\UserBundle\Entity\User;

class AccessTokenRepository extends EntityRepository
{
    /**
     * @param AccessToken $entity
     * @param bool        $flush
     */
    public function save(AccessToken $entity, $flush = true)
    {
        $this->getEntityManager()->persist($entity);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param AccessToken $entity
     * @param bool        $flush
     */
    public function remove(AccessToken $entity, $flush = true)
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
        $qb = $this->createQueryBuilder('access_token')
            ->where('access_token.user = :pUserId')
            ->andWhere('access_token.default = :pFalse')
            ->andWhere('access_token.custom = :pTrue')
            ->setParameter('pFalse', false)
            ->setParameter('pTrue', true)
            ->setParameter('pUserId', $user->getId());

        return $qb;
    }

    /**
     * @param User $user
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function qbDeleteAllByUser(User $user)
    {
        return $this->createQueryBuilder('access_token')
            ->delete()
            ->where('access_token.user = :pUserId')
            ->setParameter('pUserId', $user->getId());
    }
}
