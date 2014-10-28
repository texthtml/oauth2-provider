<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TH\OAuth2;

use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class OAuth2EntryPoint implements AuthenticationEntryPointInterface
{
    private $realm;

    public function __construct($realm)
    {
        $this->realm = $realm;
    }

    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $response = new Response();
        $response->setStatusCode(401);
        $response->headers->set('WWW-Authenticate', 'Bearer realm="'.$this->realm.'"');

        return $response;
    }
}
