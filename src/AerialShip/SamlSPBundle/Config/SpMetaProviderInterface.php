<?php

namespace AerialShip\SamlSPBundle\Config;


use AerialShip\LightSaml\Meta\SpMeta;
use Symfony\Component\HttpFoundation\Request;

interface SpMetaProviderInterface
{
    /**
     * @param Request $request
     * @return SpMeta
     */
    public function getSpMeta(Request $request);

} 