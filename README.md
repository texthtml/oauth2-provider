Oauth2 Provider
===============

[![Build Status](https://travis-ci.org/texthtml/oauth2-provider.svg?branch=master)](https://travis-ci.org/texthtml/oauth2-provider)
[![Latest Stable Version](https://poser.pugx.org/texthtml/oauth2-provider/v/stable.svg)](https://packagist.org/packages/texthtml/oauth2-provider)
[![License](https://poser.pugx.org/texthtml/oauth2-provider/license.svg)](https://packagist.org/packages/texthtml/oauth2-provider)
[![Total Downloads](https://poser.pugx.org/texthtml/oauth2-provider/downloads.svg)](https://packagist.org/packages/texthtml/oauth2-provider)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/texthtml/oauth2-provider/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/texthtml/oauth2-provider/?branch=master)

[OAuth2 Provider on Packagist](https://packagist.org/packages/texthtml/oauth2-provider)

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

$app->register(new Silex\Provider\ServiceControllerServiceProvider, [
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

$app['security.entry_point.api.oauth2.realm'] = 'My App';

$app->mount('/auth/', $app['oauth2.server_provider']);
```
