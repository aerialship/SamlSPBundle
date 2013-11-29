<?php

namespace AerialShip\SamlSPBundle\State\Authn;


interface AuthnStateStoreInterface
{
    /**
     * @param AuthnState $state
     * @return void
     */
    public function set(AuthnState $state);

    /**
     * @param string $id
     * @return AuthnState|null
     */
    public function get($id);

    /**
     * @param AuthnState $state
     * @return bool
     */
    public function remove(AuthnState $state);


    /**
     * @return void
     */
    public function clear();
}
