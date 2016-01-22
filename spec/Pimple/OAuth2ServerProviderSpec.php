<?php

namespace spec\TH\OAuth2\Pimple;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pimple\Container;

class OAuth2ServerProviderSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('TH\OAuth2\Pimple\OAuth2ServerProvider');
        $this->shouldImplement('Pimple\ServiceProviderInterface');
    }

    public function it_registers_oauth2_security_services(Container $container)
    {
        $callable = Argument::type('callable');
        $container->protect($callable)->willReturnArgument();

        $factoryIdentifier = 'security.authentication_listener.factory.oauth2';
        $viewPath = realpath(__DIR__ . '/../../src/Pimple') . '/../../views/authorize.php';

        $container->offsetSet($factoryIdentifier, $callable)->shouldBeCalled();
        $container->offsetSet('oauth2_server.storage', $callable)->shouldBeCalled();
        $container->offsetSet('oauth2_server.storage.default', $callable)->shouldBeCalled();
        $container->offsetSet('oauth2_server.storage.types', ['client', 'access_token'])->shouldBeCalled();
        $container->offsetSet('oauth2_server.storage.access_token', $callable)->shouldBeCalled();
        $container->offsetSet('oauth2_server.storage.authorization_code', $callable)->shouldBeCalled();
        $container->offsetSet('oauth2_server.storage.client_credentials', $callable)->shouldBeCalled();
        $container->offsetSet('oauth2_server.storage.client', $callable)->shouldBeCalled();
        $container->offsetSet('oauth2_server.storage.refresh_token', $callable)->shouldBeCalled();
        $container->offsetSet('oauth2_server.storage.user_credentials', $callable)->shouldBeCalled();
        $container->offsetSet('oauth2_server.storage.user_claims', $callable)->shouldBeCalled();
        $container->offsetSet('oauth2_server.storage.public_key', $callable)->shouldBeCalled();
        $container->offsetSet('oauth2_server.storage.jwt_bearer', $callable)->shouldBeCalled();
        $container->offsetSet('oauth2_server.storage.scope', $callable)->shouldBeCalled();
        $container->offsetSet('oauth2_server.config', $callable)->shouldBeCalled();
        $container->offsetSet('oauth2_server.grant_types', $callable)->shouldBeCalled();
        $container->offsetSet('oauth2_server.response_types', $callable)->shouldBeCalled();
        $container->offsetSet('oauth2_server.token_type', $callable)->shouldBeCalled();
        $container->offsetSet('oauth2_server.scope_util', $callable)->shouldBeCalled();
        $container->offsetSet('oauth2_server.client_assertion_type', $callable)->shouldBeCalled();
        $container->offsetSet('oauth2_server.parameters', [])->shouldBeCalled();
        $container->offsetSet('oauth2_server', $callable)->shouldBeCalled();
        $container->offsetSet('oauth2_server.authorize_renderer', $callable)->shouldBeCalled();
        $container->offsetSet('oauth2_server.authorize_renderer.view', $viewPath)->shouldBeCalled();
        $container->offsetSet('oauth2_server.controllers_as_service', false)->shouldBeCalled();
        $container->offsetSet('oauth2_server.controllers.authorize', $callable)->shouldBeCalled();
        $container->offsetSet('oauth2_server.controllers.authorize_validator', $callable)->shouldBeCalled();
        $container->offsetSet('oauth2_server.controllers.authorize_handler', $callable)->shouldBeCalled();
        $container->offsetSet('oauth2_server.controllers.token', $callable)->shouldBeCalled();

        $this->register($container);
    }
}
