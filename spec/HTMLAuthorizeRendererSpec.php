<?php

namespace spec\TH\OAuth2;

use PhpSpec\ObjectBehavior;
use TH\OAuth2\OAuth2Token;
use VirtualFileSystem\FileSystem;

class HTMLAuthorizeRendererSpec extends ObjectBehavior
{
    private $fileSystem;
    private $viewPath;

    public function let()
    {
        $this->fileSystem = new FileSystem();
        $this->viewPath = $this->fileSystem->path('/view.php');
        $this->beConstructedWith($this->viewPath);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('TH\OAuth2\HTMLAuthorizeRenderer');
        $this->shouldImplement('TH\OAuth2\AuthorizeRenderer');
    }

    public function it_renders()
    {
        $formUrl = '$formUrl';
        $clientId = '$clientId';
        $responseType = '$responseType';
        $user = '$user';

        file_put_contents($this->viewPath, <<<PHP
<?php
echo $formUrl, ',', $clientId, ',', $responseType, ',', $user;
PHP
        );

        $response = $this->render($formUrl, $clientId, $responseType, $user);

        $response->shouldBe('$formUrl,$clientId,$responseType,$user');
    }
}
