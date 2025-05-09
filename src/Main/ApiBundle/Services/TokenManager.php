<?php

namespace Main\ApiBundle\Services;

use FOS\OAuthServerBundle\Entity\AccessTokenManager;
use Main\ApiBundle\Entity\AccessToken;
use Main\ApiBundle\Entity\AccessTokenRepository;
use Main\ApiBundle\Entity\Client;
use Main\ApiBundle\Entity\ClientRepository;
use Main\AppBundle\Services\StringManager;
use Main\UserBundle\Entity\User;

class TokenManager
{
    /**
     * @var AccessTokenRepository
     */
    protected $accessTokenRepository;

    /**
     * @var ClientRepository
     */
    protected $clientRepository;

    /**
     * @var AccessTokenManager
     */
    protected $accessTokenManager;

    /**
     * @param AccessTokenRepository $accessTokenRepository
     * @param ClientRepository      $clientRepository
     * @param AccessTokenManager    $accessTokenManager
     */
    public function __construct(
        AccessTokenRepository $accessTokenRepository,
        ClientRepository $clientRepository,
        AccessTokenManager $accessTokenManager)
    {
        $this->accessTokenRepository = $accessTokenRepository;
        $this->clientRepository = $clientRepository;
        $this->accessTokenManager = $accessTokenManager;
    }

    /**
     * @return null|Client
     */
    public function getDefaultClient()
    {
        return $this->clientRepository->find(1);
    }

    /**
     * @param AccessToken $accessToken
     */
    public function createAccessToken(AccessToken $accessToken)
    {
        $this->accessTokenRepository->save($accessToken);
    }

    /**
     * @param AccessToken $accessToken
     */
    public function updateAccessToken(AccessToken $accessToken)
    {
        $this->accessTokenRepository->save($accessToken);
    }

    /**
     * @param AccessToken $accessToken
     */
    public function removeAccessToken(AccessToken $accessToken)
    {
        $this->accessTokenRepository->remove($accessToken);
    }

    /**
     * @return AccessToken
     */
    public function createAccessTokenEntity()
    {
        return $this->accessTokenManager->createToken();
    }

    /**
     * @param User $user
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function qbAllByUser(User $user)
    {
        return $this->accessTokenRepository->qbAllByUser($user);
    }

    /**
     * @return null|string
     */
    public function generateToken()
    {
        $accessToken = null;
        $token = null;
        $first = true;

        while ($first || $accessToken instanceof AccessToken) {
            $first = false;

            $token = StringManager::generateString(64);

            $accessToken = $this->accessTokenRepository->findOneBy([
                'token' => $token,
            ]);
        }

        return $token;
    }

    /**
     * @param $token
     *
     * @return null|AccessToken
     */
    public function getOneOrNullByToken($token)
    {
        return $this->accessTokenRepository->findOneBy([
            'token' => $token,
        ]);
    }

    /**
     * @param User $user
     */
    public function deleteAllTokensByUser(User $user)
    {
        $this->accessTokenRepository->qbDeleteAllByUser($user)->getQuery()->execute();
    }
}
