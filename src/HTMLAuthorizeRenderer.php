<?php

namespace TH\OAuth2;

class HTMLAuthorizeRenderer implements AuthorizeRenderer
{
    private $view;

    public function __construct($view)
    {
        $this->view = $view;
    }

    public function render($formUrl, $clientId, $responseType, $user)
    {
        ob_start();
        include $this->view;
        $response = ob_get_contents();
        ob_end_clean();
        return $response;
    }
}
