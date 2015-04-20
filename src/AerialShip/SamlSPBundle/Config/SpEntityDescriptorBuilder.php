<?php

namespace AerialShip\SamlSPBundle\Config;

use AerialShip\LightSaml\Bindings;
use AerialShip\LightSaml\Model\Metadata\EntityDescriptor;
use AerialShip\LightSaml\Model\Metadata\KeyDescriptor;
use AerialShip\LightSaml\Model\Metadata\Service\AssertionConsumerService;
use AerialShip\LightSaml\Model\Metadata\Service\SingleLogoutService;
use AerialShip\LightSaml\Model\Metadata\SpSsoDescriptor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\HttpUtils;

class SpEntityDescriptorBuilder implements EntityDescriptorProviderInterface
{
    /** @var  string */
    protected $authenticationServiceID;

    /** @var SPSigningProviderInterface  */
    protected $signingProvider;

    /** @var  array */
    protected $config;

    /** @var  string */
    protected $checkPath;

    /** @var  string */
    protected $logoutPath;


    /** @var  HttpUtils */
    protected $httpUtils;


    /** @var  Request */
    protected $request;


    /** @var  EntityDescriptor */
    protected $entityDescriptor;



    public function __construct(
        $authenticationServiceID,
        SPSigningProviderInterface $signingProvider,
        array $config,
        $checkPath,
        $logoutPath,
        HttpUtils $httpUtils = null
    ) {
        if (!isset($config['base_url']) && !$httpUtils) {
            throw new \RuntimeException('If config base_url is not set, then httpUtils are required');
        }
        if (!isset($config['entity_id'])) {
            throw new \RuntimeException('Missing required config entity_id');
        }

        if (!isset($config['want_assertions_signed'])) {
            $config['want_assertions_signed'] = false;
        }

        $this->authenticationServiceID = $authenticationServiceID;
        $this->signingProvider = $signingProvider;
        $this->config = $config;
        $this->checkPath = $checkPath;
        $this->logoutPath = $logoutPath;
        $this->httpUtils = $httpUtils;
    }



    /**
     * @return string
     */
    public function getAuthenticationServiceID()
    {
        return $this->authenticationServiceID;
    }


    /**
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }


    /**
     * @return EntityDescriptor
     */
    public function getEntityDescriptor()
    {
        if (!$this->entityDescriptor) {
            $this->build();
        }
        return $this->entityDescriptor;
    }



    protected function build()
    {
        $this->entityDescriptor = new EntityDescriptor($this->config['entity_id']);
        $sp = new SpSsoDescriptor();
        $this->entityDescriptor->addItem($sp);
        $sp->setWantAssertionsSigned($this->config['want_assertions_signed']);

        if ($this->signingProvider->isEnabled()) {
            $sp->addKeyDescriptor(new KeyDescriptor('signing', $this->signingProvider->getCertificate()));
        }

        $slo = new SingleLogoutService();
        $sp->addService($slo);
        $slo->setBinding(Bindings::SAML2_HTTP_REDIRECT);
        $slo->setLocation($this->buildPath($this->logoutPath));

        $slo = new SingleLogoutService();
        $sp->addService($slo);
        $slo->setBinding(Bindings::SAML2_HTTP_POST);
        $slo->setLocation($this->buildPath($this->logoutPath));

        $sp->addService(
            new AssertionConsumerService(
                Bindings::SAML2_HTTP_POST,
                $this->buildPath($this->checkPath),
                0
            )
        );
        $sp->addService(
            new AssertionConsumerService(
                Bindings::SAML2_HTTP_REDIRECT,
                $this->buildPath($this->checkPath),
                1
            )
        );
    }


    /**
     * @param string $path
     * @return string
     * @throws \RuntimeException
     */
    protected function buildPath($path)
    {
        if (isset($this->config['base_url']) && $this->config['base_url']) {
            return $this->config['base_url'] . $path;
        } else {
            if (!$this->request) {
                throw new \RuntimeException('Request not set');
            }

            return $this->httpUtils->generateUri($this->request, $path);
        }
    }
}
