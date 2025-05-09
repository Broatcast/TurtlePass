<?php

namespace UniqueLibs\FOSUserRecaptchaProtectionBundle\Entity;

use Doctrine\ORM\EntityRepository;
use UniqueLibs\FOSUserRecaptchaProtectionBundle\Model\InvalidLoginInterface;

/**
 * @author Marvin Rind <kontakt@marvinrind.de>
 */
abstract class InvalidLoginRepository extends EntityRepository implements InvalidLoginRepositoryInterface
{
    /**
     * @param InvalidLoginInterface $invalidLogin
     * @param bool                  $flush
     */
    public function persist(InvalidLoginInterface $invalidLogin, $flush = true)
    {
        $this->getEntityManager()->persist($invalidLogin);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param InvalidLoginInterface $invalidLogin
     * @param bool                  $flush
     */
    public function remove(InvalidLoginInterface $invalidLogin, $flush = true)
    {
        $this->getEntityManager()->remove($invalidLogin);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param string $ipAddress
     *
     * @return InvalidLoginInterface|null
     */
    public function findInvalidLoginByIpAddress($ipAddress)
    {
        $dateTime = new \DateTime();
        $dateTime->add(new \DateInterval('P1D'));

        return $this->createQueryBuilder('login_fail')
            ->where('login_fail.ip = :pIpAddress')
            ->andWhere('login_fail.lastFailDate < :pDateTime')
            ->setParameter('pIpAddress', inet_pton($ipAddress))
            ->setParameter('pDateTime', $dateTime)
            ->getQuery()->getOneOrNullResult();
    }
}
