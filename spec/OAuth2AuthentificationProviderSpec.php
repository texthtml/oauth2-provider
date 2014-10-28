<?php

namespace spec\TH\OAuth2;

use PhpSpec\ObjectBehavior;
use TH\OAuth2\OAuth2Token;

class OAuth2AuthentificationProviderSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('TH\OAuth2\OAuth2AuthentificationProvider');
        $this->shouldImplement('Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface');
    }

    public function it_supports_OAuth2Token(OAuth2Token $token)
    {
        $this->supports($token)->shouldReturn(true);
    }

    public function it_authenticates(OAuth2Token $token)
    {
        $this->authenticate($token)->shouldReturn($token);
    }
}
