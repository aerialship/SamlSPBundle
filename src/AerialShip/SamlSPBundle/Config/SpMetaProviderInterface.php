<?php

namespace AerialShip\SamlSPBundle\Config;


use AerialShip\LightSaml\Meta\SpMeta;
use Symfony\Component\HttpFoundation\Request;

interface SpMetaProviderInterface
{
    /**
     * @return SpMeta
     */
    public function getSpMeta();

} 