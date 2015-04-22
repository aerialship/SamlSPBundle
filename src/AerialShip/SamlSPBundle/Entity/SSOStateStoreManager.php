<?php

namespace AerialShip\SamlSPBundle\Entity;

use AerialShip\SamlSPBundle\State\SSO\DoctrineStateStore;

/**
 * You should extend AerialShip\SamlSPBundle\State\SSO\DoctrineStateStore because this class will be removed.
 * Its here just for backward compatibility.
 *
 * Class SSOStateStoreManager
 * @package AerialShip\SamlSPBundle\Entity
 *
 * @deprecated this class was moved to AerialShip\SamlSPBundle\State\SSO\DoctrineStateStore
 */
class SSOStateStoreManager extends DoctrineStateStore
{

}
