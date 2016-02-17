<?php

namespace TH\OAuth2;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class OAuth2Token extends AbstractToken implements TokenInterface
{
    private $credentials;
    private $providerKey;
    private $client;

    public function __construct($client, $user, $credentials, $providerKey, array $roles = [], array $scopes = [])
    {
        parent::__construct($roles);

        $this->client = $client;

        if (empty($providerKey)) {
            throw new \InvalidArgumentException('$providerKey must not be empty.');
        }

        if (null !== $user) {
            $this->setUser($user);
        }
        $this->credentials = $credentials;
        $this->providerKey = $providerKey;
        $this->setAttribute('scopes', $scopes);

        $this->setAuthenticated(count($roles) > 0);
    }


    public function getCredentials()
    {
        return $this->credentials;
    }

    public function getProviderKey()
    {
        return $this->providerKey;
    }

    public function getClient()
    {
        return $this->client;
    }
}
