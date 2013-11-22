<?php

namespace AerialShip\SamlSPBundle\Config;


class MetaProvider
{

    /** @var  string */
    protected $providerID;

    /** @var  string */
    protected $authenticationService;

    /** @var  EntityDescriptorProviderInterface */
    protected $idpProvider;

    /** @var  SpMetaProviderInterface */
    protected $spMetaProvider;


    /**
     * @param string $providerID
     * @param string $name
     * @param EntityDescriptorProviderInterface $idpProvider
     * @param SpMetaProviderInterface $spMetaProvider
     */
    function __construct($providerID, $name, EntityDescriptorProviderInterface $idpProvider, SpMetaProviderInterface $spMetaProvider)
    {
        $this->providerID = $providerID;
        $this->authenticationService = $name;
        $this->idpProvider = $idpProvider;
        $this->spMetaProvider = $spMetaProvider;
    }


    /**
     * @return string
     */
    public function getProviderID() {
        return $this->providerID;
    }

    /**
     * @return string
     */
    public function getAuthenticationService() {
        return $this->authenticationService;
    }

    /**
     * @return \AerialShip\SamlSPBundle\Config\EntityDescriptorProviderInterface
     */
    public function getIdpProvider() {
        return $this->idpProvider;
    }

    /**
     * @return \AerialShip\SamlSPBundle\Config\SpMetaProviderInterface
     */
    public function getSpMetaProvider() {
        return $this->spMetaProvider;
    }





}