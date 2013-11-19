<?php

namespace AerialShip\SamlSPBundle\State\Authn;

use AerialShip\SamlSPBundle\State\StateInterface;
use AerialShip\SamlSPBundle\State\StateStoreInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


class StateStoreSession implements StateStoreInterface
{
    /** @var \Symfony\Component\HttpFoundation\Session\SessionInterface  */
    protected $session;

    /** @var string  */
    protected $providerID;



    /**
     * @param SessionInterface $session
     * @param string $providerID
     */
    function __construct(SessionInterface $session, $providerID) {
        $this->session = $session;
        $this->providerID = $providerID;
    }


    /**
     * @param StateInterface $state
     * @throws \InvalidArgumentException
     * @return void
     */
    function set(StateInterface $state) {
        $key = "saml_state_{$this->providerID}_".$state->getStateID();
        $this->session->set($key, $state);
    }

    /**
     * @param string $id
     * @return mixed|null
     */
    function get($id) {
        $key = "saml_state_{$this->providerID}_{$id}";
        $result = $this->session->get($key);
        if ($result instanceof StateInterface) {
            return $result;
        }
        return null;
    }

    /**
     * @param string $id
     * @return bool
     */
    function remove($id) {
        $key = "saml_state_{$this->providerID}_{$id}";
        $value = $this->session->remove($key);
        return (bool)$value;
    }

} 