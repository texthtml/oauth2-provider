<?php

namespace spec\TH\OAuth2;

use PhpSpec\ObjectBehavior;
use TH\OAuth2\OAuth2Token;

class OAuth2TokenSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('client_id', 'user_name', 'access_token', 'provider_key');
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('TH\OAuth2\OAuth2Token');
        $this->shouldImplement('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
    }

    public function it_get_credentials()
    {
        $this->getCredentials()->shouldReturn('access_token');
    }

    public function it_get_user_name()
    {
        $this->getUsername()->shouldReturn('user_name');
    }

    public function it_get_provider_key()
    {
        $this->getProviderKey()->shouldReturn('provider_key');
    }
}
