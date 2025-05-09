<?php

namespace Main\PasswordBundle\Security;

use Main\PasswordBundle\Entity\Password;
use Main\PasswordBundle\Entity\PasswordShareLink;
use Main\PasswordBundle\Services\PasswordAccessManager;
use Main\PasswordBundle\Services\PasswordShareManager;
use Main\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PasswordShareLinkVoter extends Voter
{
    const VIEW = 'passwordsharelink_view';
    const WRITE = 'passwordsharelink_write';

    const CREATE = 'passwordsharelink_create';
    const REVOKE = 'passwordsharelink_revoke';
    const LIST_VIEW = 'passwordsharelink_list_view';

    /**
     * @var PasswordShareManager
     */
    protected $passwordShareManager;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var PasswordAccessManager
     */
    protected $passwordAccessManager;

    /**
     * @param RequestStack          $requestStack
     * @param PasswordShareManager  $passwordShareManager
     * @param PasswordAccessManager $passwordAccessManager
     */
    public function __construct(RequestStack $requestStack, PasswordShareManager $passwordShareManager, PasswordAccessManager $passwordAccessManager)
    {
        $this->passwordShareManager = $passwordShareManager;
        $this->requestStack = $requestStack;
        $this->passwordAccessManager = $passwordAccessManager;
    }

    /**
     * @param string $attribute
     * @param mixed  $subject
     *
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::VIEW, self::WRITE, self::CREATE, self::REVOKE, self::LIST_VIEW])) {
            return false;
        }

        if (!$subject instanceof PasswordShareLink && !$subject instanceof Password) {
            return false;
        }

        return true;
    }

    /**
     * @param string            $attribute
     * @param PasswordShareLink $subject
     * @param TokenInterface    $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        // special rights without users
        if (!$user instanceof User && !in_array($attribute, [self::VIEW, self::WRITE])) {
            return false;
        }

        /** @var PasswordShareLink|Password $subject */
        $entity = $subject;

        $key = $this->requestStack->getCurrentRequest()->get('token');

        switch ($attribute) {
            case self::VIEW:
                return $this->passwordShareManager->validateAccess($entity, $key);
            case self::WRITE:
                return $this->passwordShareManager->validateWriteAccess($entity, $key);
            case self::CREATE:
            case self::REVOKE:
            case self::LIST_VIEW:
                return $this->passwordAccessManager->hasUserAdminAccessOnPassword($entity, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }
}
