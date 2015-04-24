<?php

namespace AerialShip\SamlSPBundle\Entity;

use AerialShip\SamlSPBundle\Model\SSOState;
use Doctrine\ORM\Mapping as ORM;

/**
 * @deprecated Use \AerialShip\SamlSPBundle\Model\SSOState
 *
 * @ORM\HasLifecycleCallbacks
 */
abstract class SSOStateEntity extends SSOState
{
    /**
     * @var string
     * @ORM\Column(type="string", length=32, name="provider_id")
     */
    protected $providerID;

    /**
     * @var string
     * @ORM\Column(type="string", length=32, name="auth_svc_name")
     */
    protected $authenticationServiceName;

    /**
     * @var string
     * @ORM\Column(type="string", length=64, name="session_index", nullable=true)
     */
    protected $sessionIndex;

    /**
     * @var string
     * @ORM\Column(type="string", length=64, name="name_id")
     */
    protected $nameID;

    /**
     * @var string
     * @ORM\Column(type="string", length=64, name="name_id_format")
     */
    protected $nameIDFormat;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", name="created_on")
     */
    protected $createdOn;
}
