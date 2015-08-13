<?php

namespace TH\OAuth2\Controllers;

use Symfony\Component\HttpFoundation\Request;
use OAuth2\Controller\TokenControllerInterface;
use OAuth2\HttpFoundationBridge\Request as BridgeRequest;
use OAuth2\HttpFoundationBridge\Response as BridgeResponse;

class TokenHandler
{
    private $oauth2TokenController;

    public function __construct(TokenControllerInterface $oauth2TokenController)
    {
        $this->oauth2TokenController = $oauth2TokenController;
    }

    public function __invoke(Request $request)
    {
        $request = BridgeRequest::createFromRequest($request);
        $response = new BridgeResponse();
        $this->oauth2TokenController->handleTokenRequest($request, $response);
        return $response;
    }
}
