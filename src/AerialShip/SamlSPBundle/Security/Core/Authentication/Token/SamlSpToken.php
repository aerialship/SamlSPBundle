<?php

namespace AerialShip\SamlSPBundle\Security\Core\Authentication\Token;

use AerialShip\SamlSPBundle\Bridge\SamlSpInfo;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class SamlSpToken extends AbstractToken
{
    const ATTRIBUTE_NAME_ID = 'saml_name_id';
    const ATTRIBUTE_NAME_ID_FORMAT = 'saml_name_id_format';
    const ATTRIBUTE_SESSION_INDEX = 'saml_session_index';


    /** @var string */
    private $providerKey;

    /** @var  SamlSpInfo */
    private $samlSpInfo;


    public function __construct($providerKey, array $roles = array())
    {
        parent::__construct($roles);
        // If the user has roles, consider it authenticated
        $this->setAuthenticated(count($roles) > 0);
        $this->providerKey = $providerKey;
    }


    public function getProviderKey()
    {
        return $this->providerKey;
    }



    public function getCredentials()
    {
        return '';
    }




    public function setSamlSpInfo(SamlSpInfo $info)
    {
        $this->samlSpInfo = $info;

        if ($info->getNameID()) {
            $this->setAttribute(self::ATTRIBUTE_NAME_ID, $info->getNameID()->getValue());
            $this->setAttribute(self::ATTRIBUTE_NAME_ID_FORMAT, $info->getNameID()->getFormat());
        }
        if ($info->getAttributes()) {
            foreach ($info->getAttributes() as $attribute) {
                $value = $attribute->getValues();
                if (count($value) == 1) {
                    $value = array_shift($value);
                }
                $this->setAttribute($attribute->getName(), $value);
            }
        }
        if ($info->getAuthnStatement()) {
            $this->setAttribute(self::ATTRIBUTE_SESSION_INDEX, $info->getAuthnStatement()->getSessionIndex());
        }
    }

    /**
     * @return \AerialShip\SamlSPBundle\Bridge\SamlSpInfo
     */
    public function getSamlSpInfo()
    {
        return $this->samlSpInfo;
    }

    public function serialize()
    {
        return serialize(array($this->providerKey, $this->samlSpInfo, parent::serialize()));
    }

    public function unserialize($serialized)
    {
        list($this->providerKey, $this->samlSpInfo, $parentStr) = unserialize($serialized);
        parent::unserialize($parentStr);
    }
}
