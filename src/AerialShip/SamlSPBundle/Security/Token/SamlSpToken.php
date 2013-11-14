<?php

namespace AerialShip\SamlSPBundle\Security\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class SamlSpToken extends AbstractToken
{
    /** @var string */
    private $providerKey;



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



}