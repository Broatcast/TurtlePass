<?php

namespace Main\PasswordBundle\Entity;

use Doctrine\ORM\EntityRepository;

class PasswordShareLinkRepository extends EntityRepository
{
    /**
     * @param PasswordShareLink $passwordShareLink
     * @param bool              $flush
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(PasswordShareLink $passwordShareLink, $flush = true)
    {
        $this->getEntityManager()->persist($passwordShareLink);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param int $id
     *
     * @return array
     */
    public function findActiveShareLinkById($id)
    {
        $qb = $this->createQueryBuilder('password_share_link');

        $qb
            ->andWhere('password_share_link.id = :pId')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->isNull('password_share_link.validTo'),
                    $qb->expr()->gte('password_share_link.validTo', 'pNow')
                )
            )
            ->setParameter('pId', $id)
            ->setParameter('pNow', date('Y-m-d H:i:s'))
        ;

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Password $password
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function qbShareLinkByPassword(Password $password)
    {
        $qb = $this->createQueryBuilder('password_share_link');

        $qb
            ->andWhere('password_share_link.password = :pPasswordId')
            ->setParameter('pPasswordId', $password->getId())
            ->orderBy('password_share_link.createDate', 'DESC')
        ;

        return $qb;
    }
}