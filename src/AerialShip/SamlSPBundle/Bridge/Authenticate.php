<?php

namespace AerialShip\SamlSPBundle\Bridge;

use AerialShip\LightSaml\Binding\HttpRedirect;
use AerialShip\LightSaml\Meta\AuthnRequestBuilder;
use AerialShip\SamlSPBundle\Config\EntityDescriptorProviderInterface;
use AerialShip\SamlSPBundle\Config\SpMetaProviderInterface;
use AerialShip\SamlSPBundle\RelyingParty\RelyingPartyInterface;
use AerialShip\SamlSPBundle\State\SamlState;
use AerialShip\SamlSPBundle\State\StateStoreInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;


class Authenticate implements RelyingPartyInterface
{
    /** @var  EntityDescriptorProviderInterface */
    protected $spProvider;

    /** @var  EntityDescriptorProviderInterface */
    protected $idpProvider;

    /** @var  SpMetaProviderInterface */
    protected $spMetaProvider;

    /** @var \AerialShip\SamlSPBundle\State\StateStoreInterface  */
    protected $stateStore;


    public function __construct(EntityDescriptorProviderInterface $spProvider,
        EntityDescriptorProviderInterface $idpProvider,
        SpMetaProviderInterface $spMetaProvider,
        StateStoreInterface $stateStore
    ) {
        $this->spProvider = $spProvider;
        $this->idpProvider = $idpProvider;
        $this->spMetaProvider = $spMetaProvider;
        $this->stateStore = $stateStore;
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

        $spED = $this->spProvider->getEntityDescriptor($request);
        $idpED = $this->idpProvider->getEntityDescriptor($request);
        $spMeta = $this->spMetaProvider->getSpMeta($request);

        $builder = new AuthnRequestBuilder($spED, $idpED, $spMeta);
        $message = $builder->build();

        $binding = new HttpRedirect();
        /** @var \AerialShip\LightSaml\Binding\RedirectResponse $resp */
        $bindingResponse = $binding->send($message);

        $state = new SamlState();
        $state->setId($message->getID());
        $state->setDestination($message->getDestination());
        $this->stateStore->setState($state);

        $result = new RedirectResponse($bindingResponse->getUrl());
        return $result;
    }

}
