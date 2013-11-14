<?php

namespace AerialShip\SamlSPBundle\Bridge;


class SamlSpResponse implements \Serializable
{
    /** @var string */
    protected $identity;

    /** @var array */
    protected $attributes;

    /** @var string */
    protected $logoutURL;


    /**
     * @param string $identity
     * @param array $attributes
     * @param $logoutURL
     */
    public function __construct($identity, array $attributes = array(), $logoutURL)
    {
        $this->identity = $identity;
        $this->attributes = $attributes;
        $this->logoutURL = $logoutURL;
    }

    /**
     * @return string
     */
    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array($this->identity, $this->attributes));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list($this->identity, $this->attributes) = unserialize($serialized);
    }
}