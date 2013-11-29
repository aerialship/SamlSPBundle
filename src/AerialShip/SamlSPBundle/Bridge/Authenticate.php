<?php

namespace AerialShip\SamlSPBundle\Bridge;

use AerialShip\LightSaml\Binding\HttpRedirect;
use AerialShip\LightSaml\Meta\AuthnRequestBuilder;
use AerialShip\SamlSPBundle\Config\EntityDescriptorProviderInterface;
use AerialShip\SamlSPBundle\Config\MetaProviderCollection;
use AerialShip\SamlSPBundle\Config\SpEntityDescriptorBuilder;
use AerialShip\SamlSPBundle\Config\SpMetaProviderInterface;
use AerialShip\SamlSPBundle\RelyingParty\RelyingPartyInterface;
use AerialShip\SamlSPBundle\State\Authn\AuthnState;
use AerialShip\SamlSPBundle\State\Authn\AuthnStateStoreInterface;
use AerialShip\SamlSPBundle\State\SamlState;
use AerialShip\SamlSPBundle\State\SSO\SSOStateStoreInterface;
use AerialShip\SamlSPBundle\State\StateStoreInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\HttpUtils;


class Authenticate implements RelyingPartyInterface
{
    /** @var  SpEntityDescriptorBuilder */
    protected $spProvider;

    /** @var  MetaProviderCollection */
    protected $metaProviders;

    /** @var  AuthnStateStoreInterface */
    protected $authnStore;

    /** @var \Symfony\Component\Security\Http\HttpUtils  */
    protected $httpUtils;



    public function __construct(SpEntityDescriptorBuilder $spProvider,
        MetaProviderCollection $metaProviders,
        AuthnStateStoreInterface $authnStore,
        HttpUtils $httpUtils
    ) {
        $this->spProvider = $spProvider;
        $this->metaProviders = $metaProviders;
        $this->authnStore = $authnStore;
        $this->httpUtils = $httpUtils;
    }



    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return bool
     */
    public function supports(Request $request)
    {
        $result = $request->attributes->get('login_path') == $request->getPathInfo();
        return $result;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @throws \InvalidArgumentException if cannot manage the Request
     * @return \Symfony\Component\HttpFoundation\Response|SamlSpInfo
     */
    public function manage(Request $request)
    {
        if (false == $this->supports($request)) {
            throw new \InvalidArgumentException('Unsupported request');
        }

        $metaProvider = $this->metaProviders->findByAS($request->query->get('as'));
        if (!$metaProvider) {
            return new RedirectResponse($this->httpUtils->generateUri($request, $request->attributes->get('discovery_path')));
        }

        $this->spProvider->setRequest($request);
        $spED = $this->spProvider->getEntityDescriptor($request);

        $idpED = $metaProvider->getIdpProvider()->getEntityDescriptor();
        $spMeta = $metaProvider->getSpMetaProvider()->getSpMeta();

        $builder = new AuthnRequestBuilder($spED, $idpED, $spMeta);
        $message = $builder->build();

        $binding = new HttpRedirect();
        /** @var \AerialShip\LightSaml\Binding\RedirectResponse $resp */
        $bindingResponse = $binding->send($message);

        $state = new AuthnState();
        $state->setId($message->getID());
        $state->setDestination($metaProvider->getIdpProvider()->getEntityDescriptor()->getEntityID());
        $this->authnStore->set($state);

        $result = new RedirectResponse($bindingResponse->getUrl());
        return $result;
    }

}
