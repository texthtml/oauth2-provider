<?php

namespace TH\OAuth2\Pimple;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use OAuth2\Server;
use TH\OAuth2\OAuth2AuthentificationProvider;
use TH\OAuth2\OAuth2EntryPoint;
use TH\OAuth2\OAuth2AuthenticationListener;
use TH\OAuth2\Controllers;
use TH\OAuth2\HTMLAuthorizeRenderer;
use OAuth2\Storage\Pdo;

class OAuth2ServerProvider implements ServiceProviderInterface, ControllerProviderInterface
{
    private $storagesTypes = [
        'access_token',
        'authorization_code',
        'client_credentials',
        'client',
        'refresh_token',
        'user_credentials',
        'user_claims',
        'public_key',
        'jwt_bearer',
        'scope',
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
            $container['security.authentication_provider.'.$name.'.dao'] = function () {
                return new OAuth2AuthentificationProvider;
            };
        }
    }

    private function registerAuthenticationListener(Container $container, $name)
    {
        if (!isset($container['security.authentication_listener.'.$name.'.oauth2'])) {
            $authListener = function () use ($container, $name) {
                return new OAuth2AuthenticationListener(
                    $container['oauth2_server'],
                    $container['security'],
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
            return new Pdo($container['oauth2_server.storage.pdo_connection']);
        };

        $container['oauth2_server.storage.types'] = ['client', 'access_token'];

        $container['oauth2_server.storage'] = function (Container $container) {
            $storages = [];
            foreach ($container['oauth2_server.storage.types'] as $storageType) {
                $storages[$storageType] = $container['oauth2_server.storage.'.$storageType];
            }
            return $storages;
        };

        foreach ($this->storagesTypes as $storageType) {
            $container['oauth2_server.storage.'.$storageType] = function (Container $container) {
                return $container['oauth2_server.storage.default'];
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

    /**
     * @inherit
     */
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];
        $authorizeHandlerController = new Controllers\AuthorizeHandler(
            $app['oauth2_server']->getAuthorizeController(),
            $app['oauth2_server.authorize_renderer']
        );
        $controllers->post('/authorize', $authorizeHandlerController)->bind('oauth2_authorize_handler');
        $authorizeValidatorController = new Controllers\AuthorizeValidator(
            $app['url_generator'],
            $app['oauth2_server']->getAuthorizeController(),
            $app['oauth2_server.authorize_renderer']
        );
        $controllers->get('/authorize', $authorizeValidatorController)->bind('oauth2_authorize_validator');
        $tokenHandlerController = new Controllers\TokenHandler($app['oauth2_server']->getTokenController());
        $controllers->post('/token', $tokenHandlerController)->bind('oauth2_token_handler');

        return $controllers;
    }
}
