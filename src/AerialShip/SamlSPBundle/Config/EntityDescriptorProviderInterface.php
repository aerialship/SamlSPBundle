<?php

namespace AerialShip\SamlSPBundle\Config;

use AerialShip\LightSaml\Model\Metadata\EntityDescriptor;

interface EntityDescriptorProviderInterface
{

    /**
     * @return EntityDescriptor
     */
    public function getEntityDescriptor();
} 
