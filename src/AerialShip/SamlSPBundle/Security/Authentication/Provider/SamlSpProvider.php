<?php

namespace AerialShip\SamlSPBundle\Security\Authentication\Provider;

use AerialShip\SamlSPBundle\Security\Token\SamlSpToken;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;


class SamlSpProvider implements AuthenticationProviderInterface
{
    /** @var string */
    protected $providerKey;

    /** @var null|\Symfony\Component\Security\Core\User\UserProviderInterface */
    protected $userProvider;

    /** @var null|\Symfony\Component\Security\Core\User\UserCheckerInterface */
    protected $userChecker;



    public function __construct($providerKey,
            UserProviderInterface $userProvider = null,
            UserCheckerInterface $userChecker = null
    ) {
        if (null !== $userProvider && null === $userChecker) {
            throw new \InvalidArgumentException('$userChecker cannot be null, if $userProvider is not null');
        }
        $this->providerKey = $providerKey;
        $this->userProvider = $userProvider;
        $this->userChecker = $userChecker;
    }


    /**
     * Attempts to authenticate a TokenInterface object.
     * @param TokenInterface $token The TokenInterface instance to authenticate
     * @return TokenInterface An authenticated TokenInterface instance, never null
     * @throws AuthenticationException if the authentication fails
     */
    public function authenticate(TokenInterface $token) {
        if (false == $this->supports($token)) {
            return null;
        }

        if ($token->getUser() instanceof UserInterface) {
            return $this->createAuthenticatedToken(
                $token->getAttributes(),
                $token->getUser()->getRoles(),
                $token->getUser()
            );
        }

        throw new AuthenticationException('not implemented');
    }


    /**
     * Checks whether this provider supports the given token.
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @return bool   true if the implementation supports the Token, false otherwise
     */
    public function supports(TokenInterface $token) {
        return $token instanceof SamlSpToken && $this->providerKey === $token->getProviderKey();
    }



    /**
     * @param array $attributes
     * @param array $roles
     * @param mixed $user
     * @return SamlSpToken
     */
    protected function createAuthenticatedToken(array $attributes, array $roles, $user) {
        if ($user instanceof UserInterface) {
            $this->userChecker->checkPostAuth($user);
        }
        $newToken = new SamlSpToken($this->providerKey, $roles);
        $newToken->setUser($user);
        $newToken->setAttributes($attributes);
        $newToken->setAuthenticated(true);
        return $newToken;
    }

}