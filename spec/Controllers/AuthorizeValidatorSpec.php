<?php

namespace spec\TH\OAuth2\Controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use OAuth2\Controller\AuthorizeControllerInterface;
use OAuth2\HttpFoundationBridge\Response as BridgeResponse;
use TH\OAuth2\AuthorizeRenderer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AuthorizeValidatorSpec extends ObjectBehavior
{
    public function let(
        UrlGeneratorInterface $urlGenerator,
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
        $this->beConstructedWith($urlGenerator, $oauth2AuthorizeController, $authorizeRenderer);

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

    public function it_rejects_invalid_request(
        AuthorizeControllerInterface $oauth2AuthorizeController,
        Application $app,
        Request $request
    ) {
        $responseArgument = Argument::type('OAuth2\HttpFoundationBridge\Response');
        $requestArgument = Argument::type('OAuth2\HttpFoundationBridge\Request');
        $oauth2AuthorizeController->validateAuthorizeRequest($requestArgument, $responseArgument)->willReturn(false);

        $response = $this->__invoke($app, $request);
        $response->shouldHaveType('OAuth2\HttpFoundationBridge\Response');
        $response->getStatusCode()->shouldReturn(200);
    }

    public function it_renders_authorize_view(
        UrlGeneratorInterface $urlGenerator,
        AuthorizeControllerInterface $oauth2AuthorizeController,
        AuthorizeRenderer $authorizeRenderer,
        Application $app,
        Request $request,
        ParameterBag $queryBag,
        BridgeResponse $response,
        TokenStorageInterface $tokenStorage
    ) {
        $responseArgument = Argument::type('OAuth2\HttpFoundationBridge\Response');
        $requestArgument = Argument::type('OAuth2\HttpFoundationBridge\Request');
        $oauth2AuthorizeController->validateAuthorizeRequest($requestArgument, $responseArgument)->willReturn(true);


        $urlGenerator->generate('oauth2_authorize_handler', Argument::any())->willReturn('/url');
        $queryBag->all()->willReturn([
            'client_id' => 'clientId',
            'response_type' => 'responseType',
        ]);

        $app->offsetGet('security.token_storage')->willReturn($tokenStorage);
        $authorizeRenderer->render(
            '/url',
            'clientId',
            'responseType',
            null
        )->willReturn($response);

        $this->__invoke($app, $request)->shouldReturn($response);
    }

    public function it_renders_authenticated_authorize_view(
        UrlGeneratorInterface $urlGenerator,
        AuthorizeControllerInterface $oauth2AuthorizeController,
        AuthorizeRenderer $authorizeRenderer,
        Application $app,
        Request $request,
        ParameterBag $queryBag,
        BridgeResponse $response,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token
    ) {
        $responseArgument = Argument::type('OAuth2\HttpFoundationBridge\Response');
        $requestArgument = Argument::type('OAuth2\HttpFoundationBridge\Request');
        $oauth2AuthorizeController->validateAuthorizeRequest($requestArgument, $responseArgument)->willReturn(true);


        $urlGenerator->generate('oauth2_authorize_handler', Argument::any())->willReturn('/url');
        $queryBag->all()->willReturn([
            'client_id' => 'clientId',
            'response_type' => 'responseType',
        ]);

        $app->offsetGet('security.token_storage')->willReturn($tokenStorage);
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn('user');
        $authorizeRenderer->render(
            '/url',
            'clientId',
            'responseType',
            'user'
        )->willReturn($response);

        $this->__invoke($app, $request)->shouldReturn($response);
    }
}
