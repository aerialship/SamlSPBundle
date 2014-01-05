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
        $nameID = new NameID();
        $nameID->setValue($nameIDValue);
        $nameID->setFormat($nameIDFormat);

        $arrAttributes = array();
        foreach ($attributes as $name=>$value) {
            $a = new Attribute();
            $a->setName($name);
            if (!is_array($value)) {
                $value = array($value);
            }
            $a->setValues($value);
            $arrAttributes[] = $a;
        }

        $authnStatement = new AuthnStatement();
        $authnStatement->setSessionIndex($sessionIndex);

        $result = new SamlSpInfo('authServiceID', $nameID, $arrAttributes, $authnStatement);
        return $result;
    }

} 