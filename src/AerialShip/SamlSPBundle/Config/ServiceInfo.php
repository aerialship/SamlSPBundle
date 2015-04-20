<?php

namespace AerialShip\SamlSPBundle\Config;

class ServiceInfo
{

    /** @var  string */
    protected $providerID;

    /** @var  string */
    protected $authenticationService;

    /** @var  EntityDescriptorProviderInterface */
    protected $spProvider;

    /** @var  EntityDescriptorProviderInterface */
    protected $idpProvider;

    /** @var  SpMetaProviderInterface */
    protected $spMetaProvider;

    /** @var  SPSigningProviderInterface */
    protected $spSigningProvider;


    /**
     * @param string $providerID
     * @param string $name
     * @param EntityDescriptorProviderInterface $spProvider
     * @param EntityDescriptorProviderInterface $idpProvider
     * @param SpMetaProviderInterface $spMetaProvider
     * @param SPSigningProviderInterface $spSigningProvider
     */
    function __construct(
        $providerID,
        $name,
        EntityDescriptorProviderInterface $spProvider,
        EntityDescriptorProviderInterface $idpProvider,
        SpMetaProviderInterface $spMetaProvider,
        SPSigningProviderInterface $spSigningProvider
    ) {
        $this->providerID = $providerID;
        $this->authenticationService = $name;
        $this->spProvider = $spProvider;
        $this->idpProvider = $idpProvider;
        $this->spMetaProvider = $spMetaProvider;
        $this->spSigningProvider = $spSigningProvider;
    }


    /**
     * @return string
     */
    public function getProviderID()
    {
        return $this->providerID;
    }

    /**
     * @return string
     */
    public function getAuthenticationService()
    {
        return $this->authenticationService;
    }

    /**
     * @return \AerialShip\SamlSPBundle\Config\SpEntityDescriptorBuilder
     */
    public function getSpProvider()
    {
        return $this->spProvider;
    }

    /**
     * @return \AerialShip\SamlSPBundle\Config\EntityDescriptorProviderInterface
     */
    public function getIdpProvider()
    {
        return $this->idpProvider;
    }

    /**
     * @return \AerialShip\SamlSPBundle\Config\SpMetaProviderInterface
     */
    public function getSpMetaProvider()
    {
        return $this->spMetaProvider;
    }

    /**
     * @return \AerialShip\SamlSPBundle\Config\SPSigningProviderInterface
     */
    public function getSpSigningProvider()
    {
        return $this->spSigningProvider;
    }
}
