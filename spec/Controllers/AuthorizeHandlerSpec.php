<?php

namespace spec\TH\OAuth2\Controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use OAuth2\Controller\AuthorizeControllerInterface;
use TH\OAuth2\AuthorizeRenderer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AuthorizeHandlerSpec extends ObjectBehavior
{
    public function let(
        AuthorizeControllerInterface $oauth2AuthorizeController,
        AuthorizeRenderer $authorizeRenderer,
        Request $request,
        ParameterBag $queryBag,
        ParameterBag $requestBag,
        ParameterBag $attributesBag,
        ParameterBag $cookiesBag,
        ParameterBag $filesBag,
        ParameterBag $serverBag
    ) {
        $this->beConstructedWith($oauth2AuthorizeController, $authorizeRenderer);

        $queryBag->all()->willReturn([]);
        $requestBag->all()->willReturn([]);
        $attributesBag->all()->willReturn([]);
        $cookiesBag->all()->willReturn([]);
        $filesBag->all()->willReturn([]);
        $serverBag->all()->willReturn([]);

        $request->query      = $queryBag;
        $request->request    = $requestBag;
        $request->attributes = $attributesBag;
        $request->cookies    = $cookiesBag;
        $request->files      = $filesBag;
        $request->server     = $serverBag;
    }

    public function it_handles_authorization_approval(
        AuthorizeControllerInterface $oauth2AuthorizeController,
        ParameterBag $requestBag,
        Application $app,
        Request $request,
        TokenStorageInterface $tokenStorage
    ) {
        $app->offsetGet('security.token_storage')->willReturn($tokenStorage);
        $requestBag->all()->willReturn(['authorize' => '1']);

        $responseArgument = Argument::type('OAuth2\HttpFoundationBridge\Response');
        $requestArgument = Argument::type('OAuth2\HttpFoundationBridge\Request');
        $oauth2AuthorizeController->handleAuthorizeRequest(
            $requestArgument,
            $responseArgument,
            true,
            null
        )->shouldBeCalled();

        $response = $this->__invoke($app, $request);
        $response->shouldHaveType('OAuth2\HttpFoundationBridge\Response');
        $response->getStatusCode()->shouldReturn(200);
    }

    public function it_handles_authorization_refusal(
        AuthorizeControllerInterface $oauth2AuthorizeController,
        ParameterBag $requestBag,
        Application $app,
        Request $request,
        TokenStorageInterface $tokenStorage
    ) {
        $app->offsetGet('security.token_storage')->willReturn($tokenStorage);
        $requestBag->all()->willReturn(['authorize' => '0']);

        $responseArgument = Argument::type('OAuth2\HttpFoundationBridge\Response');
        $requestArgument = Argument::type('OAuth2\HttpFoundationBridge\Request');
        $oauth2AuthorizeController->handleAuthorizeRequest(
            $requestArgument,
            $responseArgument,
            false,
            null
        )->shouldBeCalled();

        $response = $this->__invoke($app, $request);
        $response->shouldHaveType('OAuth2\HttpFoundationBridge\Response');
        $response->getStatusCode()->shouldReturn(200);
    }
}
