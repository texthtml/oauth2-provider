<?php

namespace TH\OAuth2;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use OAuth2\Server;
use OAuth2\HttpFoundationBridge\Request as BridgeRequest;
use OAuth2\HttpFoundationBridge\Response as BridgeResponse;
use Psr\Log\LoggerInterface;

class OAuth2AuthenticationListener
{
    private $oauth2Server;
    private $tokenStorage;
    private $authenticationManager;
    private $providerKey;
    private $authenticationEntryPoint;
    private $logger;
    private $ignoreFailure;

    public function __construct(
        Server $oauth2Server,
        TokenStorageInterface $tokenStorage,
        AuthenticationManagerInterface $authenticationManager,
        $providerKey,
        AuthenticationEntryPointInterface $authenticationEntryPoint,
        LoggerInterface $logger
    ) {
        $this->oauth2Server = $oauth2Server;

        if (empty($providerKey)) {
            throw new \InvalidArgumentException('$providerKey must not be empty.');
        }

        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->providerKey = $providerKey;
        $this->authenticationEntryPoint = $authenticationEntryPoint;
        $this->logger = $logger;
        $this->ignoreFailure = false;
    }

    /**
     * Handles basic authentication.
     *
     * @param GetResponseEvent $event A GetResponseEvent instance
     */
    public function handle(GetResponseEvent $event)
    {
        $request = BridgeRequest::createFromRequest($event->getRequest());
        $response = new BridgeResponse;

        if (!$this->oauth2Server->verifyResourceRequest($request, $response)) {
            return;
        }

        try {
            $token = $this->oauth2Server->getAccessTokenData($request);
            $token = $this->authenticationManager->authenticate(
                new OAuth2Token($token['client_id'], $token['user_id'], $token['access_token'], $this->providerKey)
            );
            $this->tokenStorage->setToken($token);
        } catch (AuthenticationException $failed) {
            $this->handleAuthenticationError($event, $request, $failed);
        }
    }

    private function handleAuthenticationError(
        GetResponseEvent $event,
        BridgeRequest $request,
        AuthenticationException $failed
    ) {
        $token = $this->tokenStorage->getToken();
        if ($token instanceof OAuth2Token) {
            $this->tokenStorage->setToken(null);
        }

        $this->logger->info(sprintf('Authentication request failed : %s', $failed->getMessage()));

        if ($this->ignoreFailure) {
            return;
        }

        $event->setResponse($this->authenticationEntryPoint->start($request, $failed));
    }
}
