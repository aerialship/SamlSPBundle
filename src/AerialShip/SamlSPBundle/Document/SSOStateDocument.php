<?php

namespace AerialShip\SamlSPBundle\Document;

use AerialShip\SamlSPBundle\State\SSO\SSOState;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;


/**
 * @ODM\HasLifecycleCallbacks
 */
abstract class SSOStateDocument extends SSOState
{
    /**
     * @var string
     * @ODM\String(name="provider_id")
     */
    protected $providerID;

    /**
     * @var string
     * @ODM\String(name="auth_svc_name")
     */
    protected $authenticationServiceName;

    /**
     * @var string
     * @ODM\String(name="session_index")
     */
    protected $sessionIndex;

    /**
     * @var string
     * @ODM\String(name="name_id")
     */
    protected $nameID;

    /**
     * @var string
     * @ODM\String(name="name_id_format")
     */
    protected $nameIDFormat;

    /**
     * @var \DateTime
     * @ODM\Date(name="created_on")
     */
    protected $createdOn;


    /**
     * @param \DateTime $createdOn
     */
    public function setCreatedOn($createdOn)
    {
        $this->createdOn = $createdOn;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedOn()
    {
        return $this->createdOn;
    }
}
