<?php

namespace AerialShip\SamlSPBundle\Entity;

use AerialShip\SamlSPBundle\State\SSO\SSOState;
use Doctrine\ORM\Mapping as ORM;


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
     * @ORM\Column(type="string", length=64, name="session_index")
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

} 