<?php

namespace AerialShip\SamlSPBundle\Config;

use AerialShip\LightSaml\Model\Metadata\EntityDescriptor;
use Symfony\Component\HttpFoundation\Request;


interface EntityDescriptorProviderInterface
{

    /**
     * @return EntityDescriptor
     */
    public function getEntityDescriptor();

} 