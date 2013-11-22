<?php

namespace AerialShip\SamlSPBundle\Bridge;

use AerialShip\LightSaml\Model\Assertion\Attribute;
use AerialShip\LightSaml\Model\Assertion\AuthnStatement;
use AerialShip\LightSaml\Model\Assertion\NameID;


class SamlSpInfo implements \Serializable
{
    /** @var NameID */
    protected $nameID;

    /** @var Attribute[] */
    protected $attributes;

    /** @var  AuthnStatement */
    protected $authnStatement;



    /**
     * @param NameID|null $nameID
     * @param Attribute[] $attributes
     * @param \AerialShip\LightSaml\Model\Assertion\AuthnStatement $authnStatement
     */
    public function __construct(NameID $nameID = null, array $attributes = null, AuthnStatement $authnStatement = null)
    {
        $this->nameID = $nameID;
        $this->attributes = $attributes === null ? array() : $attributes;
        $this->authnStatement = $authnStatement;
    }


    /**
     * @return \AerialShip\LightSaml\Model\Assertion\Attribute[]
     */
    public function getAttributes() {
        return $this->attributes;
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
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array($this->nameID, $this->attributes, $this->authnStatement));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list($this->nameID, $this->attributes, $this->authnStatement) = unserialize($serialized);
    }
}