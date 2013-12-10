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
     * @param string $nameID
     * @return SSOState[]
     */
    function getAllByNameID($providerID, $authenticationServiceName, $nameID);

    /**
     * @param string $providerID
     * @param string $authenticationServiceName
     * @param string $nameID
     * @param string $sessionIndex
     * @return SSOState
     */
    function getOneByNameIDSessionIndex($providerID, $authenticationServiceName, $nameID, $sessionIndex);

    /**
     * @param SSOState $state
     * @return bool
     */
    function remove(SSOState $state);

} 