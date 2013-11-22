<?php

namespace AerialShip\SamlSPBundle\Config;

use AerialShip\LightSaml\Bindings;
use AerialShip\LightSaml\Model\Metadata\EntityDescriptor;
use AerialShip\LightSaml\Model\Metadata\Service\AssertionConsumerService;
use AerialShip\LightSaml\Model\Metadata\Service\SingleLogoutService;
use AerialShip\LightSaml\Model\Metadata\SpSsoDescriptor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\HttpUtils;


class SpEntityDescriptorBuilder implements EntityDescriptorProviderInterface
{
    /** @var  array */
    protected $config;

    /** @var  HttpUtils */
    protected $httpUtils;

    /** @var  Request */
    protected $request;

    /** @var  EntityDescriptor */
    protected $entityDescriptor;



    function __construct(array $config, HttpUtils $httpUtils) {
        $this->config = $config;
        $this->httpUtils = $httpUtils;
    }


    public function setRequest(Request $request) {
        $this->request = $request;
    }


    /**
     * @return EntityDescriptor
     */
    public function getEntityDescriptor() {
        if (!$this->entityDescriptor) {
            $this->build();
        }
        return $this->entityDescriptor;
    }



    protected function build() {
        if (!$this->request) {
            throw new \RuntimeException('Request not set');
        }

        $this->entityDescriptor = new EntityDescriptor($this->config['sp']['entity_id']);
        $sp = new SpSsoDescriptor();
        $this->entityDescriptor->addItem($sp);
        $sp->setWantAssertionsSigned($this->config['sp']['want_assertions_signed']);

        $slo = new SingleLogoutService();
        $sp->addService($slo);
        $slo->setBinding(Bindings::SAML2_HTTP_REDIRECT);
        $slo->setLocation($this->httpUtils->generateUri($this->request, $this->config['logout_path']));

        $sp->addService(
            new AssertionConsumerService(
                Bindings::SAML2_HTTP_POST,
                $this->httpUtils->generateUri($this->request, $this->config['check_path']),
                0
            )
        );
        $sp->addService(
            new AssertionConsumerService(
                Bindings::SAML2_HTTP_REDIRECT,
                $this->httpUtils->generateUri($this->request, $this->config['check_path']),
                1
            )
        );
    }

}
