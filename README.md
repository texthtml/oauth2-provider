Oauth2 Provider
===============

[![Build Status](https://travis-ci.org/texthtml/oauth2-provider.svg?branch=master)](https://travis-ci.org/texthtml/oauth2-provider)
[![Latest Stable Version](https://poser.pugx.org/texthtml/oauth2-provider/v/stable.svg)](https://packagist.org/packages/texthtml/oauth2-provider)
[![License](https://poser.pugx.org/texthtml/oauth2-provider/license.svg)](https://packagist.org/packages/texthtml/oauth2-provider)
[![Total Downloads](https://poser.pugx.org/texthtml/oauth2-provider/downloads.svg)](https://packagist.org/packages/texthtml/oauth2-provider)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/texthtml/oauth2-provider/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/texthtml/oauth2-provider/?branch=master)

OAuth2 Provider is a provider for Symfony Security component that can be used to build OAuth2 protected applications

Installation
------------

With Composer :

```bash
composer require texthtml/oauth2-provider
```


Usage with Silex
----------------

There is a Pimple provider you can use to secure Silex apps

```php
$app = new Silex\Application;

$oAuth2Provider = new TH\OAuth2\Pimple\OAuth2ServerProvider;
$app['security.entry_point.api.oauth2.realm'] = 'My App';
$app->register($oAuth2Provider, [
    'oauth2_server.storage.client' => function () use ($config) {
        return new TH\OAuth2\Storage\Memory\ClientMemoryStorage([
            'NICE_DEV_CLIENT' => [
                'name' => 'Nice Dev Client',
                'redirect_uri' => 'http://..../my_oauth2_callback',
            ],
        ]);
    },
    'oauth2_server.storage.pdo_connection' => function(Application $app) {
        return new PDO('...');
    },
]);
$app->mount('/auth/', $oAuth2Provider);

$app['users.provider'] = [
    // raw password is foo
    'admin' => array('ROLE_ADMIN', '5FZ2Z8QIkA7UTZ4BYkoC+GsReLf569mSKDsfods6LYQ8t+a8EW9oaircfMpmaLbPBh4FOBiiFyLfuZmTSUwzZg=='),
];

$app->register(new Silex\Provider\SecurityServiceProvider, [
    'security.firewalls' => [
        'oauth.token' => [
            'pattern' => '^/auth/token',
            'security' => false,
        ],
        'oauth.authorize' => [
            'pattern' => '^/auth/authorize',
            'http' => true,
            'users' => $app['users.provider'],
        ],
        'api' => [
            'pattern' => '^/api',
            'stateless' => true,
            'oauth2' => true,
            'security' => true,
            'users' => $app['users.provider'],
        ],
    ],
]);
```
