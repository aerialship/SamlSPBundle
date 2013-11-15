<?php

namespace AerialShip\SamlSPBundle\Config;

use AerialShip\LightSaml\Model\Metadata\EntityDescriptor;
use Symfony\Component\HttpFoundation\Request;


interface EntityDescriptorProviderInterface
{

    /**
     * @param Request $request
     * @return EntityDescriptor
     */
    public function getEntityDescriptor(Request $request);

} 