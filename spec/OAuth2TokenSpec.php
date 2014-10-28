<?php

namespace spec\TH\OAuth2;

use PhpSpec\ObjectBehavior;
use TH\OAuth2\OAuth2Token;

class OAuth2TokenSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('TH\OAuth2\OAuth2Token');
        $this->shouldImplement('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
    }

    public function it_get_credentials()
    {
        $this->getCredentials()->shouldReturn(null);
    }
}
