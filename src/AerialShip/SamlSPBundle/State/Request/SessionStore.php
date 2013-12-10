<?php

namespace AerialShip\SamlSPBundle\State\Request;

use Symfony\Component\HttpFoundation\Session\SessionInterface;


class SessionStore implements RequestStateStoreInterface
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
     * @param RequestState $state
     * @throws \InvalidArgumentException
     * @return void
     */
    function set(RequestState $state) {
        $key = "saml_state_{$this->providerID}";
        $arr = $this->session->get($key, array());
        $arr[$state->getId()] = $state;
        $this->session->set($key, $arr);
    }

    /**
     * @param string $id
     * @return RequestState|null
     */
    function get($id) {
        $result = null;
        $key = "saml_state_{$this->providerID}";
        $arr = $this->session->get($key);
        if (!is_array($arr)) {
            $arr = array();
            $this->session->set($key, $arr);
        }
        if (isset($arr[$id])) {
            $result = $arr[$id];
        }
        if ($result instanceof RequestState) {
            return $result;
        }
        return null;
    }

    /**
     * @param RequestState $state
     * @return bool
     */
    public function remove(RequestState $state)
    {
        $key = "saml_state_{$this->providerID}";
        $arr = $this->session->get($key, array());
        $result = isset($arr[$state->getId()]);
        unset($arr[$state->getId()]);
        $this->session->set($key, $arr);
        return $result;
    }

    /**
     * @return void
     */
    public function clear() {
        $key = "saml_state_{$this->providerID}";
        $this->session->set($key, array());
    }


} 