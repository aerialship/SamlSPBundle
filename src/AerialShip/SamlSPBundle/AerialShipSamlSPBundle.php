<?php

namespace AerialShip\SamlSPBundle;

use AerialShip\SamlSPBundle\DependencyInjection\Security\Factory\SamlSpFactory;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;


class AerialShipSamlSPBundle extends Bundle
{
    function build(ContainerBuilder $container) {
        parent::build($container);

        /** @var $extension SecurityExtension */
        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new SamlSpFactory());
    }

} 