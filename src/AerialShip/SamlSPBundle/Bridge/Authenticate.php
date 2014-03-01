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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\HttpUtils;


class Authenticate implements RelyingPartyInterface
{
    /** @var  ServiceInfoCollection */
    protected $serviceInfoCollection;

    /** @var  RequestStateStoreInterface */
    protected $requestStore;

    /** @var \Symfony\Component\Security\Http\HttpUtils  */
    protected $httpUtils;

    /** @var \AerialShip\SamlSPBundle\Bridge\BindingManager  */
    protected $bindingManager;


    /**
     * @param ServiceInfoCollection $serviceInfoCollection
     * @param RequestStateStoreInterface $requestStore
     * @param HttpUtils $httpUtils
     * @param BindingManager $bindingManager
     */
    public function __construct(
        ServiceInfoCollection $serviceInfoCollection,
        RequestStateStoreInterface $requestStore,
        HttpUtils $httpUtils,
        BindingManager $bindingManager
    ) {
        $this->serviceInfoCollection = $serviceInfoCollection;
        $this->requestStore = $requestStore;
        $this->httpUtils = $httpUtils;
        $this->bindingManager = $bindingManager;
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

        $binding = $this->bindingManager->instantiate($spMeta->getAuthnRequestBinding());

        $bindingResponse = $binding->send($message);
        if ($bindingResponse instanceof \AerialShip\LightSaml\Binding\RedirectResponse) {
            $result = new RedirectResponse($bindingResponse->getDestination());
        } else if ($bindingResponse instanceof \AerialShip\LightSaml\Binding\PostResponse) {
            $result = new Response($bindingResponse->render());
        } else {
            throw new \RuntimeException('Unrecognized binding response '.get_class($bindingResponse));
        }

        $state = new RequestState();
        $state->setId($message->getID());
        $state->setDestination($serviceInfo->getIdpProvider()->getEntityDescriptor()->getEntityID());
        $this->requestStore->set($state);

        return $result;
    }

}
