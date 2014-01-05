<?php

namespace AerialShip\SamlSPBundle\Bridge;

use AerialShip\LightSaml\Model\Protocol\LogoutResponse;
use AerialShip\SamlSPBundle\Config\ServiceInfoCollection;
use AerialShip\SamlSPBundle\RelyingParty\RelyingPartyInterface;
use AerialShip\SamlSPBundle\Security\Core\Authentication\Token\SamlSpToken;
use AerialShip\SamlSPBundle\State\Request\RequestStateStoreInterface;
use AerialShip\SamlSPBundle\State\SSO\SSOStateStoreInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\HttpUtils;


class LogoutReceiveResponse extends LogoutBase implements RelyingPartyInterface
{
    /** @var BindingManager */
    protected $bindingManager;

    /** @var RequestStateStoreInterface  */
    protected $requestStore;

    /** @var ServiceInfoCollection  */
    protected $serviceInfoCollection;

    /** @var \Symfony\Component\Security\Core\SecurityContextInterface  */
    protected $securityContext;


    /**
     * @param BindingManager $bindingManager
     * @param RequestStateStoreInterface $requestStore
     * @param \AerialShip\SamlSPBundle\Config\ServiceInfoCollection $serviceInfoCollection
     * @param \AerialShip\SamlSPBundle\State\SSO\SSOStateStoreInterface $ssoStore
     * @param \Symfony\Component\Security\Core\SecurityContextInterface $securityContext
     * @param \Symfony\Component\Security\Http\HttpUtils $httpUtils
     */
    public function __construct(
            BindingManager $bindingManager,
            RequestStateStoreInterface $requestStore,
            ServiceInfoCollection $serviceInfoCollection,
            SSOStateStoreInterface $ssoStore,
            SecurityContextInterface $securityContext,
            HttpUtils $httpUtils
    ) {
        parent::__construct($ssoStore, $httpUtils);
        $this->bindingManager = $bindingManager;
        $this->requestStore = $requestStore;
        $this->serviceInfoCollection = $serviceInfoCollection;
        $this->securityContext = $securityContext;
    }


    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return bool
     */
    public function supports(Request $request) {
        if ($request->attributes->get('logout_path') != $request->getPathInfo()) {
            return false;
        }
        if (!$request->get('SAMLResponse')) {
            return false;
        }
        return true;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @throws \RuntimeException
     * @throws \InvalidArgumentException if cannot manage the Request
     * @return \Symfony\Component\HttpFoundation\Response|SamlSpInfo|null
     */
    public function manage(Request $request) {
        if (!$this->supports($request)) {
            throw new \InvalidArgumentException('Unsupported request');
        }

        $logoutResponse = $this->getLogoutResponse($request);
        $this->validateRequestState($logoutResponse);
        $this->deleteSSOSession($logoutResponse);

        return $this->httpUtils->createRedirectResponse($request, $request->attributes->get('local_logout_path'));
    }


    /**
     * @param Request $request
     * @return LogoutResponse
     * @throws \InvalidArgumentException
     */
    protected function getLogoutResponse(Request $request)
    {
        /** @var  $logoutResponse LogoutResponse */
        $logoutResponse = $this->bindingManager->receive($request);
        if (!$logoutResponse || !$logoutResponse instanceof LogoutResponse) {
            throw new \InvalidArgumentException('Did not receive logout response');
        }

        return $logoutResponse;
    }

    /**
     * @param LogoutResponse $logoutResponse
     * @throws \RuntimeException
     */
    protected function validateRequestState(LogoutResponse $logoutResponse)
    {
        $state = $this->requestStore->get($logoutResponse->getInResponseTo());
        if (!$state) {
            throw new \RuntimeException('Got response to a request that was not made');
        }
        if ($state->getDestination() != $logoutResponse->getIssuer()) {
            throw new \RuntimeException('Got response from different issuer');
        }
        $this->requestStore->remove($state);
    }


    protected function deleteSSOSession(LogoutResponse $logoutResponse)
    {
        $serviceInfo = $this->serviceInfoCollection->findByIDPEntityID($logoutResponse->getIssuer());
        /** @var $token SamlSpToken */
        $token = $this->securityContext->getToken();
        if ($token && $token instanceof SamlSpToken) {
            $samlInfo = $token->getSamlSpInfo();
            if ($samlInfo) {
                $arrStates = $this->getSSOState($serviceInfo, $samlInfo->getNameID()->getValue(), $samlInfo->getAuthnStatement()->getSessionIndex());
                $this->deleteSSOState($arrStates);
            }
        }
    }

} 