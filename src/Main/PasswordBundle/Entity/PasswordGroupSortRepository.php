<?php

namespace Main\PasswordBundle\Entity;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityRepository;
use Main\UserBundle\Entity\User;

class PasswordGroupSortRepository extends EntityRepository
{
    /**
     * @param User $user
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function qbClearUserSorting(User $user)
    {
        $qb = $this->createQueryBuilder('password_group_sort');

        $qb
            ->delete()
            ->andWhere($qb->expr()->eq('password_group_sort.user', ':pUserId'))
            ->setParameter('pUserId', $user->getId())
        ;

        return $qb;
    }

    /**
     * @param array $inserts
     *
     * @throws \Exception
     */
    public function setSorting(array $inserts)
    {
        $connection = $this->getEntityManager()->getConnection();
        $connection->transactional(function ($connection) use ($inserts) {
            /** @var Connection $connection */
            foreach ($inserts as $insert) {
                $connection->insert('password_group_sorting', $insert);
            }
        });
    }
}