<?php

namespace Main\PasswordBundle\Entity\PasswordType;

use Main\PasswordBundle\Entity\Password;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Ambta\DoctrineEncryptBundle\Configuration\Encrypted;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Main\PasswordBundle\Entity\PasswordRepository")
 * @ORM\Table(name="password_bank_accounts")
 *
 * @Serializer\ExclusionPolicy("all")
 */
class BankAccountPassword extends Password
{
    /**
     * @ORM\Column(name="bank_name", type="string")
     *
     * @Assert\NotBlank()
     * @Assert\Length(max="128")
     *
     * @Encrypted()
     *
     * @Serializer\Expose()
     *
     * @var string
     */
    protected $bankName;

    /**
     * @ORM\Column(name="account_holder", type="string", nullable=true)
     *
     * @Assert\Length(max="128")
     *
     * @Encrypted()
     *
     * @Serializer\Expose()
     *
     * @var string
     */
    protected $accountHolder;

    /**
     * @ORM\Column(name="bank_code", type="string", nullable=true)
     *
     * @Assert\Length(max="40")
     *
     * @Encrypted()
     *
     * @Serializer\Expose()
     *
     * @var string
     */
    protected $bankCode;

    /**
     * @ORM\Column(name="account_number", type="string", nullable=true)
     *
     * @Assert\Length(max="40")
     *
     * @Encrypted()
     *
     * @Serializer\Expose()
     *
     * @var string
     */
    protected $accountNumber;

    /**
     * @ORM\Column(name="iban", type="string", nullable=true)
     *
     * @Assert\Length(max="40")
     *
     * @Encrypted()
     *
     * @Serializer\Expose()
     *
     * @var string
     */
    protected $iban;

    /**
     * @ORM\Column(name="pin", type="string", nullable=true)
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
    protected $bankPin;

    /**
     * @param null|int $id
     */
    public function __construct($id = null)
    {
        parent::__construct($id);

        $this->icon = 'fa-bank';
    }

    /**
     * @return string
     */
    public function getBankName()
    {
        return $this->bankName;
    }

    /**
     * @param string $bankName
     */
    public function setBankName($bankName)
    {
        $this->bankName = $bankName;
    }

    /**
     * @return string
     */
    public function getAccountHolder()
    {
        return $this->accountHolder;
    }

    /**
     * @param string $accountHolder
     */
    public function setAccountHolder($accountHolder)
    {
        $this->accountHolder = $accountHolder;
    }

    /**
     * @return string
     */
    public function getBankCode()
    {
        return $this->bankCode;
    }

    /**
     * @param string $bankCode
     */
    public function setBankCode($bankCode)
    {
        $this->bankCode = $bankCode;
    }

    /**
     * @return string
     */
    public function getAccountNumber()
    {
        return $this->accountNumber;
    }

    /**
     * @param string $accountNumber
     */
    public function setAccountNumber($accountNumber)
    {
        $this->accountNumber = $accountNumber;
    }

    /**
     * @return string
     */
    public function getIban()
    {
        return $this->iban;
    }

    /**
     * @param string $iban
     */
    public function setIban($iban)
    {
        $this->iban = $iban;
    }

    /**
     * @return string
     */
    public function getPin()
    {
        return $this->bankPin;
    }

    /**
     * @param string $pin
     */
    public function setPin($pin)
    {
        $this->bankPin = $pin;
    }
}