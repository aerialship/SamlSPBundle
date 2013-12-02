<?php

namespace AerialShip\SamlSPBundle\Security\Core\User;

use AerialShip\SamlSPBundle\Bridge\SamlSpInfo;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProviderAdapter implements UserManagerInterface
{
    /** @var \Symfony\Component\Security\Core\User\UserProviderInterface  */
    protected $userProvider;



    function __construct(UserProviderInterface $userProvider) {
        $this->userProvider = $userProvider;
    }


    /**
     * @param SamlSpInfo $samlInfo
     * @return UserInterface
     * @throws UsernameNotFoundException if the user is not found
     */
    public function loadUserBySamlInfo(SamlSpInfo $samlInfo) {
        if ($this->userProvider instanceof UserManagerInterface) {
            return $this->userProvider->loadUserBySamlInfo($samlInfo);
        } else {
            return $this->loadUserByUsername($samlInfo->getNameID()->getValue());
        }
    }

    /**
     * @param SamlSpInfo $samlInfo
     * @throws \Symfony\Component\Security\Core\Exception\UsernameNotFoundException if the user could not created
     * @return \Symfony\Component\Security\Core\User\UserInterface
     */
    public function createUserFromSamlInfo(SamlSpInfo $samlInfo) {
        if ($this->userProvider instanceof UserManagerInterface) {
            return $this->userProvider->createUserFromSamlInfo($samlInfo);
        } else {
            throw new UsernameNotFoundException('Manager does not support creation of users');
        }
    }

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $username The username
     *
     * @return UserInterface
     *
     * @see UsernameNotFoundException
     *
     * @throws UsernameNotFoundException if the user is not found
     *
     */
    public function loadUserByUsername($username) {
        return $this->userProvider->loadUserByUsername($username);
    }

    /**
     * Refreshes the user for the account interface.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     * @param UserInterface $user
     *
     * @return UserInterface
     *
     * @throws UnsupportedUserException if the account is not supported
     */
    public function refreshUser(UserInterface $user) {
        return $this->userProvider->refreshUser($user);
    }

    /**
     * Whether this provider supports the given user class
     *
     * @param string $class
     *
     * @return Boolean
     */
    public function supportsClass($class) {
        return $this->userProvider->supportsClass($class);
    }

}
