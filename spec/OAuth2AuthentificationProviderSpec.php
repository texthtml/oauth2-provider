<?php

namespace spec\TH\OAuth2;

use PhpSpec\ObjectBehavior;
use TH\OAuth2\OAuth2Token;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class OAuth2AuthentificationProviderSpec extends ObjectBehavior
{
    public function let(UserProviderInterface $userProvider, UserCheckerInterface $userChecker, OAuth2Token $token, UserInterface $user)
    {
        $userProvider->loadUserByUsername("user_name")->willReturn($user);
        $token->getProviderKey()->willReturn('provider_key');
        $token->getUsername()->willReturn('user_name');
        $token->getUser()->willReturn(null);
        $token->getCredentials()->willReturn("access_token");
        $token->getRoles()->willReturn([]);
        $token->getClient()->willReturn('client_id');
        $user->getRoles()->willReturn(["IF_FULLY_AUTHENTIFICATED"]);
        $token->getAttributes()->willReturn([]);
        $this->beConstructedWith($userProvider, $userChecker, 'provider_key');
    }

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
        $authenticatedToken = $this->authenticate($token);
        $authenticatedToken->shouldHaveType(OAuth2Token::class);
        $authenticatedToken->shouldBeAuthenticated();
    }
}
