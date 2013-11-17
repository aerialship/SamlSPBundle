<?php

namespace AerialShip\SamlSPBundle\Bridge;


use AerialShip\LightSaml\Model\Assertion\Attribute;
use AerialShip\LightSaml\Model\Assertion\NameID;

class SamlSpResponse implements \Serializable
{
    /** @var NameID */
    protected $nameID;

    /** @var Attribute[] */
    protected $attributes;


    /**
     * @param NameID|null $nameID
     * @param Attribute[] $attributes
     */
    public function __construct(NameID $nameID = null, array $attributes = null)
    {
        $this->nameID = $nameID;
        $this->attributes = $attributes === null ? array() : $attributes;
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
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array($this->nameID, $this->attributes));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list($this->nameID, $this->attributes) = unserialize($serialized);
    }
}