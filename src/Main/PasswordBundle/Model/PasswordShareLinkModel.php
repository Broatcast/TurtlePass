<?php

namespace Main\PasswordBundle\Model;

use Symfony\Component\Validator\Constraints as Assert;

class PasswordShareLinkModel
{
    /**
     * @var int
     *
     * @Assert\NotBlank()
     */
    protected $mode;

    /**
     * @var \DateTimeImmutable|null
     */
    protected $validTo;

    /**
     * @var int|null
     *
     * @Assert\GreaterThan(value="0")
     */
    protected $viewLimit;

    /**
     * @var string|null
     *
     * @Assert\Email()
     */
    protected $recipient;

    /**
     * @return int
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param int $mode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getValidTo()
    {
        return $this->validTo;
    }

    /**
     * @param \DateTimeImmutable|null $validTo
     */
    public function setValidTo($validTo = null)
    {
        $this->validTo = $validTo;
    }

    /**
     * @return int|null
     */
    public function getViewLimit()
    {
        return $this->viewLimit;
    }

    /**
     * @param int|null $viewLimit
     */
    public function setViewLimit($viewLimit)
    {
        $this->viewLimit = $viewLimit;
    }

    /**
     * @return null|string
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @param null|string $recipient
     */
    public function setRecipient($recipient = null)
    {
        $this->recipient = $recipient;
    }
}