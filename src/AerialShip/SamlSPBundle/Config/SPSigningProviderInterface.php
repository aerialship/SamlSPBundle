<?php

namespace AerialShip\SamlSPBundle\Config;

use AerialShip\LightSaml\Security\X509Certificate;

interface SPSigningProviderInterface
{
    /**
     * @return bool
     */
    public function isEnabled();

    /**
     * @return X509Certificate
     */
    public function getCertificate();

    /**
     * @return \XMLSecurityKey
     */
    public function getPrivateKey();
}
