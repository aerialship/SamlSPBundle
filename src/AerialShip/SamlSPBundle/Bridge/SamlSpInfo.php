<?php

namespace AerialShip\SamlSPBundle\Bridge;

use AerialShip\LightSaml\Model\Assertion\Attribute;
use AerialShip\LightSaml\Model\Assertion\AuthnStatement;
use AerialShip\LightSaml\Model\Assertion\NameID;


class SamlSpInfo implements \Serializable
{
    /** @var  string */
    protected $authenticationServiceID;

    /** @var NameID */
    protected $nameID;

    /** @var Attribute[] */
    protected $attributes;

    /** @var  AuthnStatement */
    protected $authnStatement;


    /**
     * @param string $authenticationServiceID
     * @param NameID|null $nameID
     * @param Attribute[] $attributes
     * @param \AerialShip\LightSaml\Model\Assertion\AuthnStatement $authnStatement
     */
    public function __construct($authenticationServiceID, NameID $nameID = null, array $attributes = null, AuthnStatement $authnStatement = null)
    {
        $this->authenticationServiceID = $authenticationServiceID;
        $this->nameID = $nameID;
        $this->attributes = $attributes === null ? array() : $attributes;
        $this->authnStatement = $authnStatement;
    }


    /**
     * @return \AerialShip\LightSaml\Model\Assertion\Attribute[]
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param Attribute $attr
     * @return SamlSpInfo
     */
    public function addAttribute(Attribute $attr)
    {
        $this->attributes[] = $attr;
        return $this;
    }

    /**
     * @param \AerialShip\LightSaml\Model\Assertion\NameID $nameID
     */
    public function setNameID($nameID) {
        $this->nameID = $nameID;
    }

    /**
     * @return \AerialShip\LightSaml\Model\Assertion\NameID
     */
    public function getNameID() {
        return $this->nameID;
    }

    /**
     * @param \AerialShip\LightSaml\Model\Assertion\AuthnStatement $authnStatement
     */
    public function setAuthnStatement($authnStatement) {
        $this->authnStatement = $authnStatement;
    }

    /**
     * @return \AerialShip\LightSaml\Model\Assertion\AuthnStatement
     */
    public function getAuthnStatement() {
        return $this->authnStatement;
    }

    /**
     * @param string $authenticationServiceID
     */
    public function setAuthenticationServiceID($authenticationServiceID) {
        $this->authenticationServiceID = $authenticationServiceID;
    }

    /**
     * @return string
     */
    public function getAuthenticationServiceID() {
        return $this->authenticationServiceID;
    }






    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array($this->authenticationServiceID, $this->nameID, $this->attributes, $this->authnStatement));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list($this->authenticationServiceID, $this->nameID, $this->attributes, $this->authnStatement) = unserialize($serialized);
    }
}