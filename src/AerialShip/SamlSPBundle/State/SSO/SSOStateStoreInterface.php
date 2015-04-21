<?php

namespace AerialShip\SamlSPBundle\State\SSO;

interface SSOStateStoreInterface
{
    /**
     * @return SSOState
     */
    public function create();

    /**
     * @param SSOState $state
     * @return void
     */
    public function set(SSOState $state);


    /**
     * @param string $providerID
     * @param string $authenticationServiceName
     * @param string $nameID
     * @return SSOState[]
     */
    public function getAllByNameID($providerID, $authenticationServiceName, $nameID);

    /**
     * @param string $providerID
     * @param string $authenticationServiceName
     * @param string $nameID
     * @param string $sessionIndex
     * @return SSOState
     */
    public function getOneByNameIDSessionIndex($providerID, $authenticationServiceName, $nameID, $sessionIndex);

    /**
     * @param SSOState $state
     * @return bool
     */
    public function remove(SSOState $state);
} 
