<?php

namespace TH\OAuth2;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Role\SwitchUserRole;

class OAuth2AuthentificationProvider implements AuthenticationProviderInterface
{
    /** @var UserProviderInterface  */
    private $userProvider;

    /** @var  UserCheckerInterface */
    private $userChecker;

    private $providerKey;

    private $hideUserNotFoundExceptions;

    public function __construct(UserProviderInterface $userProvider, UserCheckerInterface $userChecker, $providerKey, $hideUserNotFoundExceptions = true)
    {
        $this->userProvider = $userProvider;
        $this->userChecker = $userChecker;
        $this->providerKey = $providerKey;
        $this->hideUserNotFoundExceptions = $hideUserNotFoundExceptions;
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof OAuth2Token && $token->getProviderKey() == $this->providerKey;
    }

    public function authenticate(TokenInterface $token)
    {
        $user = null;
        if (!$this->supports($token)) {
            return;
        }

        $username = $token->getUsername();
        if (!empty($username)) {

            $user = $this->retrieveUser($username, $token);

            if (!$user instanceof UserInterface) {
                throw new AuthenticationServiceException('retrieveUser() must return a UserInterface.');
            }

            $this->checkUser($user);
        }

        return $this->authenticatedToken($token, $user);
    }

    private function authenticatedToken(TokenInterface $token, UserInterface $user = null)
    {
        if (!$token instanceof OAuth2Token) {
            throw new Exception ("token should be instance of OAuth2Token");
        }

        $authenticatedToken = new OAuth2Token(
            $token->getClient(),
            $user,
            $token->getCredentials(),
            $this->providerKey,
            $this->getRoles($token, $user),
            $token->hasAttribute('scopes') ? $token->getAttribute('scopes') : []
        );
        $authenticatedToken->setAttributes($token->getAttributes());

        return $authenticatedToken;
    }

    /**
     * @param string $username
     */
    private function retrieveUser($username, TokenInterface $token)
    {
        $user = $token->getUser();
        if ($user instanceof UserInterface) {
            return $user;
        }

        try {
            return $this->getUser($username);
        } catch (UsernameNotFoundException $notFound) {
            $this->hideUserNotFoundExceptions($notFound, $username);
        } catch (\Exception $repositoryProblem) {
            $ex = new AuthenticationServiceException($repositoryProblem->getMessage(), 0, $repositoryProblem);
            $ex->setToken($token);
            throw $ex;
        }
    }

    /**
     * @param string $username
     */
    private function hideUserNotFoundExceptions(UsernameNotFoundException $notFound, $username)
    {
        if ($this->hideUserNotFoundExceptions) {
            throw new BadCredentialsException('Bad credentials.', 0, $notFound);
        }
        $notFound->setUsername($username);
        throw $notFound;
    }

    private function getRoles(TokenInterface $token, UserInterface $user = null)
    {
        if (null === $user) {
            return [];
        }

        $roles = $user->getRoles();

        foreach ($token->getRoles() as $role) {
            if ($role instanceof SwitchUserRole) {
                $roles[] = $role;

                break;
            }
        }

        return $roles;
    }

    /**
     * @param string $username
     */
    private function getUser($username)
    {
        $user = $this->userProvider->loadUserByUsername($username);

        if (!$user instanceof UserInterface) {
            throw new AuthenticationServiceException('The user provider must return a UserInterface object.');
        }

        return $user;
    }


    private function checkUser(UserInterface $user)
    {
        try {
            $this->userChecker->checkPreAuth($user);
            $this->userChecker->checkPostAuth($user);
        } catch (BadCredentialsException $e) {
            if ($this->hideUserNotFoundExceptions) {
                throw new BadCredentialsException('Bad credentials.', 0, $e);
            }

            throw $e;
        }
    }

}
