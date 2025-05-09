<?php

namespace Main\PasswordBundle\Entity\PasswordType;

use Main\PasswordBundle\Entity\Password;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Ambta\DoctrineEncryptBundle\Configuration\Encrypted;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Main\PasswordBundle\Entity\PasswordRepository")
 * @ORM\Table(name="password_credit_cards")
 */
class CreditCardPassword extends Password
{
    /**
     * @ORM\Column(name="card_type", type="string", nullable=true)
     *
     * @Assert\Length(max="128")
     *
     * @Serializer\Expose()
     *
     * @var string
     */
    protected $cardType;

    /**
     * @ORM\Column(name="card_number", type="string", nullable=true)
     *
     * @Assert\Length(max="128")
     *
     * @Encrypted()
     *
     * @Serializer\Expose()
     *
     * @var string
     */
    protected $cardNumber;

    /**
     * @ORM\Column(name="card_cvc", type="string", nullable=true)
     *
     * @Assert\Length(max="128")
     *
     * @Encrypted()
     *
     * @Serializer\Expose()
     *
     * @var string
     */
    protected $cardCvc;

    /**
     * @ORM\Column(type="string", name="pin", nullable=true)
     *
     * @Assert\Length(max="128")
     *
     * @Encrypted()
     *
     * @Serializer\SerializedName("pin")
     * @Serializer\Expose()
     *
     * @var string
     */
    protected $cardPin;

    /**
     * @ORM\Column(name="valid_from", type="string", nullable=true)
     *
     * @Assert\Length(max="32")
     *
     * @Serializer\Expose()
     *
     * @var string
     */
    protected $validFrom;

    /**
     * @ORM\Column(name="valid_to", type="string", nullable=true)
     *
     * @Assert\Length(max="32")
     *
     * @Serializer\Expose()
     *
     * @var string
     */
    protected $validTo;

    /**
     * @param null|int $id
     */
    public function __construct($id = null)
    {
        parent::__construct($id);

        $this->icon = 'fa-credit-card';
    }

    /**
     * @return string
     */
    public function getCardType()
    {
        return $this->cardType;
    }

    /**
     * @param string $cardType
     */
    public function setCardType($cardType)
    {
        $this->cardType = $cardType;
    }

    /**
     * @return string
     */
    public function getCardNumber()
    {
        return $this->cardNumber;
    }

    /**
     * @param string $cardNumber
     */
    public function setCardNumber($cardNumber)
    {
        $this->cardNumber = $cardNumber;
    }

    /**
     * @return string
     */
    public function getCardCvc()
    {
        return $this->cardCvc;
    }

    /**
     * @param string $cardCvc
     */
    public function setCardCvc($cardCvc)
    {
        $this->cardCvc = $cardCvc;
    }

    /**
     * @return string
     */
    public function getPin()
    {
        return $this->cardPin;
    }

    /**
     * @param string $pin
     */
    public function setPin($pin)
    {
        $this->cardPin = $pin;
    }

    /**
     * @return string
     */
    public function getValidFrom()
    {
        return $this->validFrom;
    }

    /**
     * @param string $validFrom
     */
    public function setValidFrom($validFrom)
    {
        $this->validFrom = $validFrom;
    }

    /**
     * @return string
     */
    public function getValidTo()
    {
        return $this->validTo;
    }

    /**
     * @param string $validTo
     */
    public function setValidTo($validTo)
    {
        $this->validTo = $validTo;
    }
}