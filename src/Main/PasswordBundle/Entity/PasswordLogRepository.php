<?php

namespace Main\PasswordBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Main\UserBundle\Entity\User;

class PasswordLogRepository extends EntityRepository
{
    /**
     * @param PasswordLog $entity
     * @param bool        $flush
     */
    public function save(PasswordLog $entity, $flush = true)
    {
        $this->getEntityManager()->persist($entity);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param PasswordLog $entity
     * @param bool        $flush
     */
    public function remove(PasswordLog $entity, $flush = true)
    {
        $this->getEntityManager()->remove($entity);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param Password $password
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function qbAllByPassword(Password $password)
    {
        $qb = $this->createQueryBuilder('password_log')
            ->select('password_log, user, share_link')
            ->leftJoin('password_log.user', 'user')
            ->leftJoin('password_log.shareLink', 'share_link')
            ->andWhere('password_log.password = :pPasswordId')
            ->setParameter('pPasswordId', $password->getId())
            ->orderBy('password_log.createDate', 'DESC');

        return $qb;
    }

    /**
     * @param Password  $password
     * @param User|null $user
     * @param null      $key
     *
     * @return PasswordLog|null
     */
    public function findLastPasswordLog(Password $password, User $user = null, $key = null)
    {
        $qb = $this->createQueryBuilder('password_log');
        $qb
            ->andWhere('password_log.password = :pPasswordId')
            ->setParameter('pPasswordId', $password->getId())
            ->orderBy('password_log.createDate', 'DESC')
            ->setMaxResults(1);

        if ($user !== null) {
            $qb
                ->andWhere('password_log.user = :pUserId')
                ->setParameter('pUserId', $user->getId());
        }

        if ($key !== null) {
            $qb
                ->andWhere('password_log.key = :pKeyId')
                ->setParameter('pKeyId', $key);
        }


        return $qb->getQuery()->getOneOrNullResult();
    }
}
