<?php

namespace AerialShip\SamlSPBundle\Bridge;

use AerialShip\LightSaml\Binding\HttpRedirect;
use AerialShip\LightSaml\Meta\AuthnRequestBuilder;
use AerialShip\SamlSPBundle\Config\ServiceInfoCollection;
use AerialShip\SamlSPBundle\RelyingParty\RelyingPartyInterface;
use AerialShip\SamlSPBundle\State\Request\RequestState;
use AerialShip\SamlSPBundle\State\Request\RequestStateStoreInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\HttpUtils;


class Authenticate implements RelyingPartyInterface
{
    /** @var  ServiceInfoCollection */
    protected $serviceInfoCollection;

    /** @var  RequestStateStoreInterface */
    protected $requestStore;

    /** @var \Symfony\Component\Security\Http\HttpUtils  */
    protected $httpUtils;


    /**
     * @param ServiceInfoCollection $serviceInfoCollection
     * @param RequestStateStoreInterface $requestStore
     * @param HttpUtils $httpUtils
     */
    public function __construct(
        ServiceInfoCollection $serviceInfoCollection,
        RequestStateStoreInterface $requestStore,
        HttpUtils $httpUtils
    ) {
        $this->serviceInfoCollection = $serviceInfoCollection;
        $this->requestStore = $requestStore;
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

        $serviceInfo = $this->serviceInfoCollection->findByAS($request->query->get('as'));
        if (!$serviceInfo) {
            return new RedirectResponse($this->httpUtils->generateUri($request, $request->attributes->get('discovery_path')));
        }

        $serviceInfo->getSpProvider()->setRequest($request);
        $spED = $serviceInfo->getSpProvider()->getEntityDescriptor();

        $idpED = $serviceInfo->getIdpProvider()->getEntityDescriptor();
        $spMeta = $serviceInfo->getSpMetaProvider()->getSpMeta();

        $builder = new AuthnRequestBuilder($spED, $idpED, $spMeta);
        $message = $builder->build();

        $binding = new HttpRedirect();
        /** @var $bindingResponse \AerialShip\LightSaml\Binding\RedirectResponse */
        $bindingResponse = $binding->send($message);

        $state = new RequestState();
        $state->setId($message->getID());
        $state->setDestination($serviceInfo->getIdpProvider()->getEntityDescriptor()->getEntityID());
        $this->requestStore->set($state);

        $result = new RedirectResponse($bindingResponse->getUrl());
        return $result;
    }

}
