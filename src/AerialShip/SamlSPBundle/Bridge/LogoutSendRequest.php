<?php

namespace AerialShip\SamlSPBundle\Bridge;

use AerialShip\LightSaml\Meta\LogoutRequestBuilder;
use AerialShip\LightSaml\Model\Protocol\LogoutRequest;
use AerialShip\SamlSPBundle\Config\ServiceInfo;
use AerialShip\SamlSPBundle\Config\ServiceInfoCollection;
use AerialShip\SamlSPBundle\RelyingParty\RelyingPartyInterface;
use AerialShip\SamlSPBundle\Security\Core\Authentication\Token\SamlSpToken;
use AerialShip\SamlSPBundle\State\Request\RequestState;
use AerialShip\SamlSPBundle\State\Request\RequestStateStoreInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContextInterface;


class LogoutSendRequest implements RelyingPartyInterface
{
    /** @var \Symfony\Component\Security\Core\SecurityContextInterface  */
    protected $securityContext;

    /** @var  ServiceInfoCollection */
    protected $serviceInfoCollection;

    /** @var RequestStateStoreInterface */
    protected $requestStateStore;



    /**
     * @param SecurityContextInterface $securityContext
     * @param ServiceInfoCollection $serviceInfoCollection
     * @param \AerialShip\SamlSPBundle\State\Request\RequestStateStoreInterface $requestStateStore
     */
    public function __construct(
        SecurityContextInterface $securityContext,
        ServiceInfoCollection $serviceInfoCollection,
        RequestStateStoreInterface $requestStateStore
    ) {
        $this->securityContext = $securityContext;
        $this->serviceInfoCollection = $serviceInfoCollection;
        $this->requestStateStore = $requestStateStore;
    }



    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return bool
     */
    function supports(Request $request) {
        if ($request->attributes->get('logout_path') != $request->getPathInfo()) {
            return false;
        }
        /** @var $token SamlSpToken */
        $token = $this->securityContext->getToken();
        if (!$token || !$token instanceof SamlSpToken) {
            return false;
        }
        if (!$token->getSamlSpInfo()) {
            return false;
        }
        return true;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @throws \RuntimeException  if no signing provider set
     * @throws \InvalidArgumentException if cannot manage the Request
     * @return \Symfony\Component\HttpFoundation\Response|SamlSpInfo
     */
    function manage(Request $request) {
        if (!$this->supports($request)) {
            throw new \InvalidArgumentException('Unsupported request');
        }

        $samlInfo = $this->getSamlInfo();
        $serviceInfo = $this->getServiceInfo($samlInfo, $request);

        $builder = $this->createLogoutRequestBuilder($serviceInfo);
        $logoutRequest = $this->createLogoutRequest($builder, $serviceInfo, $samlInfo);
        $bindingResponse = $builder->send($logoutRequest);

        $this->createRequestState($logoutRequest, $serviceInfo);

        if ($bindingResponse instanceof \AerialShip\LightSaml\Binding\PostResponse) {
            return new Response($bindingResponse->render());
        } else if ($bindingResponse instanceof \AerialShip\LightSaml\Binding\RedirectResponse) {
            return new RedirectResponse($bindingResponse->getDestination());
        }

        throw new \RuntimeException('Unknown binding response '.get_class($bindingResponse));
    }


    /**
     * @return SamlSpInfo
     */
    protected function getSamlInfo()
    {
        /** @var $token SamlSpToken */
        $token = $this->securityContext->getToken();
        $samlInfo = $token->getSamlSpInfo();
        return $samlInfo;
    }


    /**
     * @param SamlSpInfo $samlInfo
     * @param Request $request
     * @return ServiceInfo
     * @throws \RuntimeException
     */
    protected function getServiceInfo(SamlSpInfo $samlInfo, Request $request)
    {
        $serviceInfo = $this->serviceInfoCollection->get($samlInfo->getAuthenticationServiceID());
        if (!$serviceInfo) {
            throw new \RuntimeException("redirect to discovery");
        }
        if (!$serviceInfo->getSpSigningProvider()->isEnabled()) {
            throw new \RuntimeException('Signing is required for Logout');
        }
        $serviceInfo->getSpProvider()->setRequest($request);

        return $serviceInfo;
    }

    /**
     * @param ServiceInfo $serviceInfo
     * @return LogoutRequestBuilder
     */
    protected function createLogoutRequestBuilder(ServiceInfo $serviceInfo)
    {
        $builder = new LogoutRequestBuilder(
            $serviceInfo->getSpProvider()->getEntityDescriptor(),
            $serviceInfo->getIdpProvider()->getEntityDescriptor(),
            $serviceInfo->getSpMetaProvider()->getSpMeta()
        );

        return $builder;
    }

    /**
     * @param LogoutRequestBuilder $builder
     * @param ServiceInfo $serviceInfo
     * @param SamlSpInfo $samlInfo
     * @return LogoutRequest
     */
    protected function createLogoutRequest(LogoutRequestBuilder $builder, ServiceInfo $serviceInfo, SamlSpInfo $samlInfo)
    {
        $logoutRequest = $builder->build(
            $samlInfo->getNameID()->getValue(),
            $samlInfo->getNameID()->getFormat(),
            $samlInfo->getAuthnStatement()->getSessionIndex()
        );
        $logoutRequest->sign($serviceInfo->getSpSigningProvider()->getCertificate(), $serviceInfo->getSpSigningProvider()->getPrivateKey());

        return $logoutRequest;
    }

    /**
     * @param LogoutRequest $request
     * @param ServiceInfo $serviceInfo
     * @return RequestState
     */
    protected function createRequestState(LogoutRequest $request, ServiceInfo $serviceInfo)
    {
        $state = new RequestState();
        $state->setId($request->getID());
        $state->setDestination($serviceInfo->getIdpProvider()->getEntityDescriptor()->getEntityID());
        $this->requestStateStore->set($state);

        return $state;
    }

} 