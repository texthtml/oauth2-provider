<?php

namespace spec\TH\OAuth2;

use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Request;

class OAuth2EntryPointSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('AppName');
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('TH\OAuth2\OAuth2EntryPoint');
        $this->shouldImplement('Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface');
    }

    public function it_starts(Request $request)
    {
        $response = $this->start($request);

        $response->shouldHaveType('Symfony\Component\HttpFoundation\Response');
        $response->getStatusCode()->shouldReturn(401);
        $response->headers->get('WWW-Authenticate')->shouldReturn('Bearer realm="AppName"');
    }
}
