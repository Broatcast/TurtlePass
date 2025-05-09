<?php

namespace Main\FOSUserRecaptchaProtectionBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class MainFOSUserRecaptchaProtectionBundle extends Bundle
{
    public function getParent()
    {
        return 'UniqueLibsFOSUserRecaptchaProtectionBundle';
    }
}
