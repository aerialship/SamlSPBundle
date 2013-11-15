<?php

namespace AerialShip\SamlSPBundle\Security\Token;

use AerialShip\LightSaml\Model\Assertion\Attribute;
use AerialShip\LightSaml\Model\Assertion\NameID;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class SamlSpToken extends AbstractToken
{
    /** @var string */
    private $providerKey;

    /** @var  NameID */
    private $nameID;


    public function __construct($providerKey, array $roles = array()) {
        parent::__construct($roles);
        // If the user has roles, consider it authenticated
        $this->setAuthenticated(count($roles) > 0);
        $this->providerKey = $providerKey;
    }


    public function getProviderKey() {
        return $this->providerKey;
    }



    public function getCredentials() {
        return '';
    }

    /**
     * @param \AerialShip\LightSaml\Model\Assertion\NameID $nameID
     */
    public function setNameID($nameID) {
        $this->nameID = $nameID;
        $this->setAttribute('nameID', $nameID->getValue());
        $this->setAttribute('nameIDFormat', $nameID->getFormat());
    }

    /**
     * @return \AerialShip\LightSaml\Model\Assertion\NameID
     */
    public function getNameID() {
        return $this->nameID;
    }




    /**
     * @param Attribute[] $attributes
     */
    public function setSamlAttributes(array $attributes) {
        $data = array();
        foreach ($attributes as $attr) {
            $name = $attr->getName();
            $value = $attr->getValues();
            if (count($value) == 1) {
                $value = array_shift($value);
            }
            $data[$name] = $value;
        }
        $this->setAttributes($data);
    }


}