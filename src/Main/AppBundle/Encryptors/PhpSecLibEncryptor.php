<?php

namespace Main\AppBundle\Encryptors;

use Ambta\DoctrineEncryptBundle\Encryptors\EncryptorInterface;
use phpseclib\Crypt\Base;
use phpseclib\Crypt\Rijndael;

class PhpSecLibEncryptor implements EncryptorInterface
{
    /**
     * @var string
     */
    private $secretKey;

    /**
     * @var Rijndael
     */
    private $cipher;

    /**
     * {@inheritdoc}
     */
    public function __construct($key)
    {
        $this->secretKey = md5($key);

        $this->cipher = new Rijndael(Base::MODE_ECB);
        $this->cipher->setBlockLength(256);
        $this->cipher->setKeyLength(256);
        $this->cipher->setKey($this->secretKey);
        $this->cipher->disablePadding();
        $this->cipher->setPreferredEngine(Base::ENGINE_INTERNAL);
    }

    /**
     * {@inheritdoc}
     */
    public function encrypt($data)
    {
        if  (is_string($data)) {
            $padded = str_pad($data, (32*(floor(strlen($data) / 32)+(strlen($data) % 32==0?2:1))), chr(32-(strlen($data) % 32)));
            return trim(base64_encode($this->cipher->encrypt($padded))). "<ENC>";
        }

        return $data;

    }

    /**
     * {@inheritdoc}
     */
    public function decrypt($data)
    {
        if (is_string($data)) {
            $data = str_replace("<ENC>", "", $data);

            return trim(preg_replace('/[\x00-\x1F\x7F]/u', "", $this->cipher->decrypt(base64_decode($data))));
        }

        return $data;
    }
}