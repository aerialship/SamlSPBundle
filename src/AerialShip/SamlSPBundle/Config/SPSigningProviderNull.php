<?php

namespace AerialShip\SamlSPBundle\Config;

use AerialShip\LightSaml\Security\X509Certificate;


class SPSigningProviderNull implements SPSigningProviderInterface
{
    /**
     * @return bool
     */
    public function isEnabled() {
        return false;
    }

    /**
     * @throws \RuntimeException
     * @return X509Certificate
     */
    public function getCertificate() {
        throw new \RuntimeException('Signing not enabled');
    }

    /**
     * @throws \RuntimeException
     * @return \XMLSecurityKey
     */
    public function getPrivateKey() {
        throw new \RuntimeException('Signing not enabled');
    }

} 