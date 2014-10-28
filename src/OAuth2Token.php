<?php

namespace TH\OAuth2;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class OAuth2Token extends AbstractToken implements TokenInterface
{
    public function getCredentials()
    {
        return null;
    }
}
