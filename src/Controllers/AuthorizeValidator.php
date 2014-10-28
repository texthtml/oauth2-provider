<?php

namespace TH\OAuth2\Controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use OAuth2\Controller\AuthorizeControllerInterface;
use OAuth2\HttpFoundationBridge\Request as BridgeRequest;
use OAuth2\HttpFoundationBridge\Response as BridgeResponse;
use TH\OAuth2\AuthorizeRenderer;

class AuthorizeValidator
{
    private $urlGenerator;

    private $oauth2AuthorizeController;

    private $authorizeRenderer;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        AuthorizeControllerInterface $oauth2AuthorizeController,
        AuthorizeRenderer $authorizeRenderer
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->oauth2AuthorizeController = $oauth2AuthorizeController;
        $this->authorizeRenderer = $authorizeRenderer;
    }

    public function __invoke(Application $app, Request $request)
    {
        $request = BridgeRequest::createFromRequest($request);
        $response = new BridgeResponse;

        if (!$this->oauth2AuthorizeController->validateAuthorizeRequest($request, $response)) {
            return $response;
        }

        $token = $app['security']->getToken();
        $user = null;

        if ($token instanceof TokenInterface) {
            $user = $token->getUser();
        }

        return $this->authorizeRenderer->render(
            $this->urlGenerator->generate('oauth2_authorize_handler', $request->query->all()),
            $request->query->get('client_id'),
            $request->query->get('response_type'),
            $user
        );
    }
}
