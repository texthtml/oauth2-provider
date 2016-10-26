<?php

namespace TH\OAuth2\Pimple;

use OAuth2\Server;
use OAuth2\Storage;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use TH\OAuth2\Controllers;
use TH\OAuth2\HTMLAuthorizeRenderer;
use TH\OAuth2\OAuth2AuthenticationListener;
use TH\OAuth2\OAuth2AuthentificationProvider;
use TH\OAuth2\OAuth2EntryPoint;

class OAuth2ServerProvider implements ServiceProviderInterface, ControllerProviderInterface
{
    private $storagesTypes = [
        'access_token'       => Storage\AccessTokenInterface::class,
        'authorization_code' => Storage\AuthorizationCodeInterface::class,
        'client_credentials' => Storage\ClientCredentialsInterface::class,
        'client'             => Storage\ClientInterface::class,
        'refresh_token'      => Storage\RefreshTokenInterface::class,
        'user_credentials'   => Storage\UserCredentialsInterface::class,
        'user_claims'        => Storage\UserClaimsInterface::class,
        'public_key'         => Storage\PublicKeyInterface::class,
        'jwt_bearer'         => Storage\JwtBearerInterface::class,
        'scope'              => Storage\ScopeInterface::class,
    ];

    /**
     * @inherit
     */
    public function register(Container $container)
    {
        $container['security.authentication_listener.factory.oauth2'] = $container->protect(
            $this->factory($container)
        );

        $container['oauth2_server'] = $this->OAuth2Server($container);

        $this->setupControllers($container);

        $container['oauth2_server.authorize_renderer.view'] = __DIR__ . '/../../views/authorize.php';

        $container['oauth2_server.authorize_renderer'] = function (Container $container) {
            return new HTMLAuthorizeRenderer($container['oauth2_server.authorize_renderer.view']);
        };
    }

    private function factory(Container $container)
    {
        return function ($name) use ($container) {
            $this->registerFactoryDeps($container, $name);

            return [
                'security.authentication_provider.'.$name.'.dao',
                'security.authentication_listener.'.$name.'.oauth2',
                'security.entry_point.'.$name.'.oauth2',
                'pre_auth'
            ];
        };
    }

    private function registerFactoryDeps(Container $container, $name)
    {
        if (!isset($container['security.entry_point.'.$name.'.oauth2.realm'])) {
            $container['security.entry_point.'.$name.'.oauth2'] = 'AppName';
        }
        if (!isset($container['security.entry_point.'.$name.'.oauth2'])) {
            $realm = $container['security.entry_point.'.$name.'.oauth2.realm'];
            $container['security.entry_point.'.$name.'.oauth2'] = new OAuth2EntryPoint($realm);
        }
        $this->registerAuthenticationListener($container, $name);
        if (!isset($container['security.authentication_provider.'.$name.'.dao'])) {
            $container['security.authentication_provider.'.$name.'.dao'] = function () use ($container, $name) {
                return new OAuth2AuthentificationProvider(
                    $container['security.user_provider.'.$name],
                    $container['security.user_checker'],
                    $name
                );
            };
        }
    }

    private function registerAuthenticationListener(Container $container, $name)
    {
        if (!isset($container['security.authentication_listener.'.$name.'.oauth2'])) {
            $authListener = function () use ($container, $name) {
                return new OAuth2AuthenticationListener(
                    $container['oauth2_server'],
                    $container['security.token_storage'],
                    $container['security.authentication_manager'],
                    $name,
                    $container['security.entry_point.'.$name.'.oauth2'],
                    $container['logger']
                );
            };
            $container['security.authentication_listener.'.$name.'.oauth2'] = $authListener;
        }
    }

    private function OAuth2Server(Container $container)
    {
        $container['oauth2_server.parameters'] = [];

        $container['oauth2_server.storage.default'] = function (Container $container) {
            return new Storage\Pdo($container['oauth2_server.storage.pdo_connection']);
        };

        $container['oauth2_server.storage.types'] = ['client', 'access_token'];

        $container['oauth2_server.storage'] = function (Container $container) {
            $storages = [];
            foreach ($container['oauth2_server.storage.types'] as $storageType) {
                $storages[$storageType] = $container['oauth2_server.storage.'.$storageType];
            }
            return $storages;
        };

        foreach ($this->storagesTypes as $storageType => $storageInterface) {
            $container['oauth2_server.storage.'.$storageType] = function (Container $container) use ($storageInterface) {
                if ($container['oauth2_server.storage.default'] instanceof $storageInterface) {
                    return $container['oauth2_server.storage.default'];
                }
            };
        }

        $container['oauth2_server.config'] = function () {
            return ['allow_implicit' => true, 'enforce_state' => false,];
        };

        $container['oauth2_server.grant_types'] = function () {
            return [];
        };

        $container['oauth2_server.response_types'] = function () {
            return [];
        };

        $container['oauth2_server.token_type'] = function () {
            return null;
        };

        $container['oauth2_server.scope_util'] = function () {
            return null;
        };

        $container['oauth2_server.client_assertion_type'] = function () {
            return null;
        };

        return function (Container $container) {
            return new Server(
                $container['oauth2_server.storage'],
                $container['oauth2_server.config'],
                $container['oauth2_server.grant_types'],
                $container['oauth2_server.response_types'],
                $container['oauth2_server.token_type'],
                $container['oauth2_server.scope_util'],
                $container['oauth2_server.client_assertion_type']
            );
        };
    }

    private function setupControllers(Container $container) {
        $container['oauth2_server.controllers_as_service'] = false;
        $container['oauth2_server.controllers.authorize'] = function (Container $container) {
            return new Controllers\AuthorizeHandler(
                $container['oauth2_server']->getAuthorizeController(),
                $container['oauth2_server.authorize_renderer']
            );
        };
        $container['oauth2_server.controllers.authorize_validator'] = function (Container $container) {
            return new Controllers\AuthorizeValidator(
                $container['url_generator'],
                $container['oauth2_server']->getAuthorizeController(),
                $container['oauth2_server.authorize_renderer']
            );
        };
        $container['oauth2_server.controllers.authorize_handler'] = function (Container $container) {
            return new Controllers\AuthorizeHandler(
                $container['oauth2_server']->getAuthorizeController(),
                $container['oauth2_server.authorize_renderer']
            );
        };
        $container['oauth2_server.controllers.token'] = function (Container $container) {
            return new Controllers\TokenHandler($container['oauth2_server']->getTokenController());
        };
    }

    /**
     * @inherit
     */
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        if ($app['oauth2_server.controllers_as_service']) {
            $controllers->post('/authorize', 'oauth2_server.controllers.authorize_handler:__invoke')
                ->bind('oauth2_authorize_handler');
            $controllers->get('/authorize', 'oauth2_server.controllers.authorize_validator:__invoke')
                ->bind('oauth2_authorize_validator');
            $controllers->post('/token', 'oauth2_server.controllers.token:__invoke')->bind('oauth2_token_handler');
        } else {
            $controllers->post('/authorize', $app['oauth2_server.controllers.authorize_handler'])
                ->bind('oauth2_authorize_handler');
            $controllers->get('/authorize', $app['oauth2_server.controllers.authorize_validator'])
                ->bind('oauth2_authorize_validator');
            $controllers->post('/token', $app['oauth2_server.controllers.token'])->bind('oauth2_token_handler');
        }

        return $controllers;
    }
}
