<?php

namespace AerialShip\SamlSPBundle\Model;

/**
 * @api
 */
abstract class SSOState implements \Serializable
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

    /** @var \DateTime */
    protected $createdOn;

    /**
     * @param string $providerID
     *
     * @return SSOState
     */
    public function setProviderID($providerID)
    {
        $this->providerID = $providerID;

        return $this;
    }

    /**
     * @return string
     */
    public function getProviderID()
    {
        return $this->providerID;
    }

    /**
     * @param string $authenticationServiceName
     *
     * @return SSOState
     */
    public function setAuthenticationServiceName($authenticationServiceName)
    {
        $this->authenticationServiceName = $authenticationServiceName;

        return $this;
    }

    /**
     * @return string
     */
    public function getAuthenticationServiceName()
    {
        return $this->authenticationServiceName;
    }

    /**
     * @param string $nameID
     *
     * @return SSOState
     */
    public function setNameID($nameID)
    {
        $this->nameID = $nameID;

        return $this;
    }

    /**
     * @return string
     */
    public function getNameID()
    {
        return $this->nameID;
    }

    /**
     * @param string $nameIDFormat
     *
     * @return SSOState
     */
    public function setNameIDFormat($nameIDFormat)
    {
        $this->nameIDFormat = $nameIDFormat;

        return $this;
    }

    /**
     * @return string
     */
    public function getNameIDFormat()
    {
        return $this->nameIDFormat;
    }

    /**
     * @param string $sessionIndex
     *
     * @return SSOState
     */
    public function setSessionIndex($sessionIndex)
    {
        $this->sessionIndex = $sessionIndex;

        return $this;
    }

    /**
     * @return string
     */
    public function getSessionIndex()
    {
        return $this->sessionIndex;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedOn()
    {
        return $this->createdOn;
    }

    /**
     * @param \DateTime $createdOn
     *
     * @return SSOState
     */
    public function setCreatedOn(\DateTime $createdOn)
    {
        $this->createdOn = $createdOn;

        return $this;
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
                $this->nameIDFormat,
                $this->createdOn,
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
        $data = unserialize($serialized);

        // add a few extra elements in the array to ensure that we have enough keys when unserializing
        // older data which does not include all properties.
        $data = array_merge($data, array_fill(0, 4, null));

        list(
            $this->providerID,
            $this->authenticationServiceName,
            $this->sessionIndex,
            $this->nameID,
            $this->nameIDFormat,
            $this->createdOn
            ) = $data;
    }
}
