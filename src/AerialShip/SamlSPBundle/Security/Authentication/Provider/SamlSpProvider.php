<?php

namespace AerialShip\SamlSPBundle\Security\Authentication\Provider;

use AerialShip\LightSaml\Model\Assertion\NameID;
use AerialShip\SamlSPBundle\Security\Token\SamlSpToken;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\User;
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
        /** @var $token SamlSpToken */
        if ($token->getUser() instanceof UserInterface) {
            return $this->createAuthenticatedToken(
                $token->getNameID(),
                $token->getAttributes(),
                $token->getUser()->getRoles(),
                $token->getUser()
            );
        }

        try {
            $user = $this->userProvider ?
                    $this->getProviderUser($token) :
                    $this->getDefaultUser($token)
            ;

            return $this->createAuthenticatedToken(
                $token->getNameID(),
                $token->getAttributes(),
                $user instanceof UserInterface ? $user->getRoles() : array(),
                $user
            );


        } catch (AuthenticationException $ex) {
            throw $ex;
        } catch (\Exception $ex) {
            throw new AuthenticationServiceException($ex->getMessage(), (int) $ex->getCode(), $ex);
        }
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
     * @param NameID $nameID
     * @param array $attributes
     * @param array $roles
     * @param mixed $user
     * @return SamlSpToken
     */
    protected function createAuthenticatedToken(NameID $nameID, array $attributes, array $roles, $user) {
        if ($user instanceof UserInterface) {
            $this->userChecker->checkPostAuth($user);
        }
        $newToken = new SamlSpToken($this->providerKey, $roles);
        $newToken->setUser($user);
        $newToken->setAttributes($attributes);
        $newToken->setNameID($nameID);
        $newToken->setAuthenticated(true);
        return $newToken;
    }

    /**
     * @param SamlSpToken $token
     * @return UserInterface
     * @throws \Exception
     * @throws \Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     * @throws \RuntimeException
     */
    private function getProviderUser(SamlSpToken $token) {
        if (!$token || !$token->getNameID() || !$token->getNameID()->getValue()) {
            throw new UsernameNotFoundException('Token contains no nameID');
        }
        try {
            $user = $this->userProvider->loadUserByUsername($token->getNameID()->getValue());
        } catch (UsernameNotFoundException $ex) {
            throw $ex;
//            if (false == $this->createIfNotExists) {
//                throw $e;
//            }
//            $user = $this->userProvider->createUserFromIdentity($identity, $attributes);
        }

        if (false == $user instanceof UserInterface) {
            throw new \RuntimeException('User provider did not return an implementation of user interface.');
        }

        return $user;
    }

    /**
     * @param \AerialShip\SamlSPBundle\Security\Token\SamlSpToken $token
     * @return UserInterface
     */
    private function getDefaultUser(SamlSpToken $token) {
        $nameID = $token && $token->getNameID() && $token->getNameID()->getValue() ? $token->getNameID()->getValue() : 'anon.';
        $result = new User($nameID, '', array('ROLE_USER'));
        return $result;
    }

}