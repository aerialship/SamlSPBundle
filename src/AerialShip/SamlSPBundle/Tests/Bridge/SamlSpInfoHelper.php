<?php

namespace AerialShip\SamlSPBundle\Tests\Bridge;

use AerialShip\LightSaml\Model\Assertion\Attribute;
use AerialShip\LightSaml\Model\Assertion\AuthnStatement;
use AerialShip\LightSaml\Model\Assertion\NameID;
use AerialShip\SamlSPBundle\Bridge\SamlSpInfo;

class SamlSpInfoHelper
{

    /**
     * @param string $nameIDValue
     * @param string $nameIDFormat
     * @return NameID
     */
    public function getNameID(
        $nameIDValue = 'nameID',
        $nameIDFormat = 'nameIDFormat'
    ) {
        $nameID = new NameID();
        $nameID->setValue($nameIDValue);
        $nameID->setFormat($nameIDFormat);

        return $nameID;
    }


    /**
     * @param Attribute[] $attributes
     * @return array
     */
    public function getAttributes(array $attributes = array('a'=>1, 'b'=>array(2,3)))
    {
        $arrAttributes = array();
        foreach ($attributes as $name => $value) {
            $a = new Attribute();
            $a->setName($name);
            if (!is_array($value)) {
                $value = array($value);
            }
            $a->setValues($value);
            $arrAttributes[] = $a;
        }

        return $arrAttributes;
    }


    /**
     * @param string $sessionIndex
     * @return AuthnStatement
     */
    public function getAuthnStatement($sessionIndex = 'session_index')
    {
        $authnStatement = new AuthnStatement();
        $authnStatement->setSessionIndex($sessionIndex);

        return $authnStatement;
    }


    /**
     * @param string $nameIDValue
     * @param string $nameIDFormat
     * @param array $attributes
     * @param string $sessionIndex
     * @return SamlSpInfo
     */
    public function getSamlSpInfo(
        $nameIDValue = 'nameID',
        $nameIDFormat = 'nameIDFormat',
        array $attributes = array('a'=>1, 'b'=>array(2,3)),
        $sessionIndex = 'session_index'
    ) {
        $nameID = $this->getNameID($nameIDValue, $nameIDFormat);

        $arrAttributes = $this->getAttributes($attributes);

        $authnStatement = $this->getAuthnStatement($sessionIndex);

        $result = new SamlSpInfo('authServiceID', $nameID, $arrAttributes, $authnStatement);
        return $result;
    }
}
