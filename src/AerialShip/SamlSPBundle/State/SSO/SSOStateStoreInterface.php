<?php

namespace AerialShip\SamlSPBundle\State\SSO;

use AerialShip\SamlSPBundle\Model\SSOState;

/**
 * @api
 */
interface SSOStateStoreInterface
{
    /**
     * @return SSOState
     */
    public function create();

    /**
     * @param SSOState $state
     *
     * @return void
     */
    public function set(SSOState $state);


    /**
     * @param string $providerID
     * @param string $authenticationServiceName
     * @param string $nameID
     *
     * @return SSOState[]
     */
    public function getAllByNameID($providerID, $authenticationServiceName, $nameID);

    /**
     * @param string $providerID
     * @param string $authenticationServiceName
     * @param string $nameID
     * @param string $sessionIndex
     *
     * @return SSOState
     */
    public function getOneByNameIDSessionIndex($providerID, $authenticationServiceName, $nameID, $sessionIndex);

    /**
     * @param SSOState $state
     *
     * @return void
     */
    public function remove(SSOState $state);
}
