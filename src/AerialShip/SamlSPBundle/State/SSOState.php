<?php

namespace AerialShip\SamlSPBundle\State;


class SSOState implements StateInterface, \Serializable
{
    protected $sessionIndex;

    protected $nameID;

    protected $nameIDFormat;


    /**
     * @param mixed $nameID
     */
    public function setNameID($nameID) {
        $this->nameID = $nameID;
    }

    /**
     * @return mixed
     */
    public function getNameID() {
        return $this->nameID;
    }

    /**
     * @param mixed $nameIDFormat
     */
    public function setNameIDFormat($nameIDFormat) {
        $this->nameIDFormat = $nameIDFormat;
    }

    /**
     * @return mixed
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
     * @return string
     */
    public function getStateID() {
        return $this->sessionIndex;
    }


    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize() {
        return serialize(array($this->sessionIndex, $this->nameID, $this->nameIDFormat));
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
    public function unserialize($serialized) {
        list($this->sessionIndex, $this->nameID, $this->nameIDFormat) = unserialize($serialized);
    }


}