<?php

namespace Main\UserBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;

class ChangePasswordModel
{
    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Length(max = 64)
     * @SecurityAssert\UserPassword()
     */
    protected $currentPassword;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Length(min = 5, max = 64)
     */
    protected $newPassword;

    /**
     * @return string
     */
    public function getCurrentPassword()
    {
        return $this->currentPassword;
    }

    /**
     * @param string $currentPassword
     */
    public function setCurrentPassword($currentPassword)
    {
        $this->currentPassword = $currentPassword;
    }

    /**
     * @return string
     */
    public function getNewPassword()
    {
        return $this->newPassword;
    }

    /**
     * @param string $newPassword
     */
    public function setNewPassword($newPassword)
    {
        $this->newPassword = $newPassword;
    }
}
