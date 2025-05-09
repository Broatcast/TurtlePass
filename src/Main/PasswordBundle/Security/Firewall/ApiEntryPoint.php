<?php

namespace Main\PasswordBundle\Security\Firewall;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class ApiEntryPoint implements AuthenticationEntryPointInterface
{
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new JsonResponse([
            'error' => JsonResponse::HTTP_FORBIDDEN,
            'message' => $authException ? $authException->getMessage() : 'Access Denied'
        ], JsonResponse::HTTP_FORBIDDEN);
    }
}