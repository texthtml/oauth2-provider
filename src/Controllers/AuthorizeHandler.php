<?php

namespace TH\OAuth2\Controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use OAuth2\Controller\AuthorizeControllerInterface;
use OAuth2\HttpFoundationBridge\Request as BridgeRequest;
use OAuth2\HttpFoundationBridge\Response as BridgeResponse;
use TH\OAuth2\AuthorizeRenderer;

class AuthorizeHandler
{
    private $oauth2AuthorizeController;

    private $authorizeRenderer;

    public function __construct(
        AuthorizeControllerInterface $oauth2AuthorizeController,
        AuthorizeRenderer $authorizeRenderer
    ) {
        $this->oauth2AuthorizeController = $oauth2AuthorizeController;
        $this->authorizeRenderer = $authorizeRenderer;
    }

    public function __invoke(Application $app, Request $request)
    {
        $token = $app['security.token_storage']->getToken();
        $user = null;

        if ($token instanceof TokenInterface) {
            $user = $token->getUser()->getUsername();
        }

        $request = BridgeRequest::createFromRequest($request);
        $response = new BridgeResponse;
        $this->oauth2AuthorizeController->handleAuthorizeRequest(
            $request,
            $response,
            (bool) $request->request->get('authorize'),
            $user
        );

        return $response;
    }
}
