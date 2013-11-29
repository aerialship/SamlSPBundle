<?php

namespace AerialShip\SamlSPBundle\State\SSO;


interface SSOStateStoreInterface
{
    /**
     * @return SSOState
     */
    function create();

    /**
     * @param SSOState $state
     * @return void
     */
    function set(SSOState $state);


    /**
     * @param string $providerID
     * @param string $authenticationServiceName
     * @param string $sessionIndex
     * @return SSOState|null
     */
    function get($providerID, $authenticationServiceName, $sessionIndex);


    /**
     * @param SSOState $state
     * @return bool
     */
    function remove(SSOState $state);

} 