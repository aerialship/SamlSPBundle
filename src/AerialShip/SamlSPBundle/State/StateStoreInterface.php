<?php

namespace AerialShip\SamlSPBundle\State;


interface StateStoreInterface
{
    /**
     * @param StateInterface $state
     * @return void
     */
    function set(StateInterface $state);

    /**
     * @param string $id
     * @return StateInterface|null
     */
    function get($id);


    /**
     * @param string $id
     * @return bool
     */
    function remove($id);

} 