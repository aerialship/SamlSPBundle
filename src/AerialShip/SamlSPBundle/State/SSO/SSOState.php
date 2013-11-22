<?php

namespace AerialShip\SamlSPBundle\State\SSO;


class SSOState implements \Serializable
{
    /** @var string */
    protected $providerID;

    /** @var string */
    protected $authenticationServiceName;

    /** @var string */
    protected $sessionIndex;

    /** @var string */
    protected $nameID;

    /** @var string */
    protected $nameIDFormat;





    /**
     * @param string $providerID
     */
    public function setProviderID($providerID) {
        $this->providerID = $providerID;
    }

    /**
     * @return string
     */
    public function getProviderID() {
        return $this->providerID;
    }

    /**
     * @param string $authenticationServiceName
     */
    public function setAuthenticationServiceName($authenticationServiceName) {
        $this->authenticationServiceName = $authenticationServiceName;
    }

    /**
     * @return string
     */
    public function getAuthenticationServiceName() {
        return $this->authenticationServiceName;
    }

    /**
     * @param string $nameID
     */
    public function setNameID($nameID) {
        $this->nameID = $nameID;
    }

    /**
     * @return string
     */
    public function getNameID() {
        return $this->nameID;
    }

    /**
     * @param string $nameIDFormat
     */
    public function setNameIDFormat($nameIDFormat) {
        $this->nameIDFormat = $nameIDFormat;
    }

    /**
     * @return string
     */
    public function getNameIDFormat() {
        return $this->nameIDFormat;
    }

    /**
     * @param mixed $sessionIndex
     */
    public function setSessionIndex($sessionIndex) {
        $this->sessionIndex = $sessionIndex;
    }

    /**
     * @return mixed
     */
    public function getSessionIndex() {
        return $this->sessionIndex;
    }





    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize(
            array(
                 $this->providerID,
                 $this->authenticationServiceName,
                 $this->sessionIndex,
                 $this->nameID,
                 $this->nameIDFormat
            )
        );
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     */
    public function unserialize($serialized)
    {
        list(
            $this->providerID,
            $this->authenticationServiceName,
            $this->sessionIndex,
            $this->nameID,
            $this->nameIDFormat
        ) = unserialize($serialized);
    }


}