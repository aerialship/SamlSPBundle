<?php

namespace AerialShip\SamlSPBundle\State\Request;

interface RequestStateStoreInterface
{
    /**
     * @param RequestState $state
     * @return void
     */
    public function set(RequestState $state);

    /**
     * @param string $id
     * @return RequestState|null
     */
    public function get($id);

    /**
     * @param RequestState $state
     * @return bool
     */
    public function remove(RequestState $state);


    /**
     * @return void
     */
    public function clear();
}
