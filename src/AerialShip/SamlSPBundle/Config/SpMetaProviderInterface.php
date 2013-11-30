<?php

namespace AerialShip\SamlSPBundle\Config;

use AerialShip\LightSaml\Meta\SpMeta;

interface SpMetaProviderInterface
{
    /**
     * @return SpMeta
     */
    public function getSpMeta();

} 