<?php

namespace TH\OAuth2;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class OAuth2AuthentificationProvider implements AuthenticationProviderInterface
{
    public function supports(TokenInterface $token)
    {
        return $token instanceof OAuth2Token;
    }

    public function authenticate(TokenInterface $token)
    {
        return $token;
    }
}
