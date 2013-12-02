<?php

namespace AerialShip\SamlSPBundle\Security\Core\User;

use AerialShip\SamlSPBundle\Bridge\SamlSpInfo;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

interface UserManagerInterface extends UserProviderInterface
{
    /**
     * @param SamlSpInfo $samlInfo
     * @return UserInterface
     * @throws UsernameNotFoundException if the user is not found
     */
    public function loadUserBySamlInfo(SamlSpInfo $samlInfo);

    /**
     * @param SamlSpInfo $samlInfo
     * @throws \Symfony\Component\Security\Core\Exception\UsernameNotFoundException if the user could not created
     * @return \Symfony\Component\Security\Core\User\UserInterface
     */
    public function createUserFromSamlInfo(SamlSpInfo $samlInfo);

}
