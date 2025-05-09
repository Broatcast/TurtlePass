<?php

namespace UniqueLibs\FOSUserRecaptchaProtectionBundle\Entity;

use Doctrine\ORM\EntityRepository;
use UniqueLibs\FOSUserRecaptchaProtectionBundle\Model\LoginAccessInterface;

/**
 * @author Marvin Rind <kontakt@marvinrind.de>
 */
abstract class LoginAccessRepository extends EntityRepository implements LoginAccessRepositoryInterface
{
    /**
     * @param LoginAccessInterface $loginAccess
     * @param bool                 $flush
     */
    public function persist(LoginAccessInterface $loginAccess, $flush = true)
    {
        $this->getEntityManager()->persist($loginAccess);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param LoginAccessInterface $loginAccess
     * @param bool                 $flush
     */
    public function remove(LoginAccessInterface $loginAccess, $flush = true)
    {
        $this->getEntityManager()->remove($loginAccess);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param string $ipAddress
     * @param bool   $whitelist
     *
     * @return LoginAccessInterface|null
     */
    public function findLoginAccessByIpAddress($ipAddress, $whitelist = false)
    {
        $qb = $this->createQueryBuilder('login_access');

        $qb
            ->andWhere('login_access.fromIp >= :pIpAddress')
            ->andWhere('login_access.toIp <= :pIpAddress')
            ->andWhere('login_access.whitelist = :pWhitelist')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->isNull('login_access.expireDate'),
                    $qb->expr()->gt('login_access.expireDate', ':pDateTime')
                )
            )
            ->setParameter('pWhitelist', $whitelist)
            ->setParameter('pIpAddress', inet_pton($ipAddress))
            ->setParameter('pDateTime', new \DateTime());

        return $qb->getQuery()->getOneOrNullResult();
    }
}
