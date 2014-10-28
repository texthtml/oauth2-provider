<?php

namespace TH\OAuth2;

interface AuthorizeRenderer
{
    /**
     * Render the authorization page
     *
     * @param  string      $formUrl      the url to direct the form to
     * @param  string      $clientId     the client id
     * @param  string      $responseType the response type
     * @param  string|null $user         the user identifier if presents
     * @return string
     */
    public function render($formUrl, $clientId, $responseType, $user);
}
