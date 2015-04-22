<?php

namespace AerialShip\SamlSPBundle\Entity;

use AerialShip\SamlSPBundle\State\SSO\SSOStateStoreManager as SSOStateManager;

/**
 * You should extend AerialShip\SamlSPBundle\State\SSO\SSOStateStoreManager because this class will be removed.
 * Its here just for backward compatibility.
 *
 * Class SSOStateStoreManager
 * @package AerialShip\SamlSPBundle\Entity
 *
 * @deprecated this class was moved to AerialShip\SamlSPBundle\State\SSO\SSOStateStoreManager
 */
class SSOStateStoreManager extends SSOStateManager
{

}
