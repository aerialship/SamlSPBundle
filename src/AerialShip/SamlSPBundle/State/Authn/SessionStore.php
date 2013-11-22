<?php

namespace AerialShip\SamlSPBundle\State\Authn;

use Symfony\Component\HttpFoundation\Session\SessionInterface;


class SessionStore implements AuthnStateStoreInterface
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
     * @param AuthnState $state
     * @throws \InvalidArgumentException
     * @return void
     */
    function set(AuthnState $state) {
        $key = "saml_state_{$this->providerID}";
        $this->session->set($key, $state);
    }

    /**
     * @param string $id
     * @return AuthnState|null
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
        if ($result instanceof AuthnState) {
            return $result;
        }
        return null;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function remove($id)
    {
        $result = false;
        $key = "saml_state_{$this->providerID}";
        $arr = $this->session->get($key);
        if (is_array($arr)) {
            $result = isset($arr[$id]);
            unset($arr[$id]);
        } else {
            $arr = array();
        }
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