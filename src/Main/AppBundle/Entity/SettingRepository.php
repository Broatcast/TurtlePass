<?php

namespace Main\AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class SettingRepository extends EntityRepository
{
    /**
     * @param Setting $entity
     * @param bool    $flush
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(Setting $entity, $flush = true)
    {
        $this->getEntityManager()->persist($entity);

        if (true === $flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function qbAll()
    {
        $qb = $this->createQueryBuilder('setting')
            ->andWhere('setting.id NOT IN (:pIds)')
            ->setParameter('pIds', Setting::getRecaptchaIds());

        return $qb;
    }
}
