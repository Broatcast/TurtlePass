<?php

namespace Main\UserBundle\Entity;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    /**
     * @param User $entity
     * @param bool $flush
     */
    public function save(User $entity, $flush = true)
    {
        $this->getEntityManager()->persist($entity);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param User $entity
     * @param bool $flush
     */
    public function remove(User $entity, $flush = true)
    {
        $this->getEntityManager()->remove($entity);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function qbAllNotDeleted()
    {
        $qb = $this->createQueryBuilder('user')
            ->where('user.deleted = :pFalse')
            ->setParameter('pFalse', false);

        return $qb;
    }

    /**
     * @param array $criteria
     *
     * @return array|User[]
     */
    public function findByUniqueCriteria(array $criteria)
    {
        return $this->_em->getRepository('MainUserBundle:User')->findBy($criteria);
    }

    /**
     * @param $canonicalizeUsername
     *
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findUserLocalUserByUsername($canonicalizeUsername)
    {
        $qb = $this->createQueryBuilder('user');

        $qb
            ->andWhere($qb->expr()->eq('user.username', ':pCanonicalizeUsername'))
            ->andWhere($qb->expr()->isInstanceOf('user', User::class))
            ->setParameter('pCanonicalizeUsername', $canonicalizeUsername)
        ;

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }
}
