<?php

namespace AerialShip\SamlSPBundle\Bridge;

use AerialShip\LightSaml\Model\Protocol\LogoutResponse;
use AerialShip\SamlSPBundle\RelyingParty\RelyingPartyInterface;
use AerialShip\SamlSPBundle\Security\Core\Token\SamlSpToken;
use AerialShip\SamlSPBundle\State\Request\RequestStateStoreInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\HttpUtils;


class LogoutReceiveResponse implements RelyingPartyInterface
{
    /** @var BindingManager */
    protected $bindingManager;

    /** @var RequestStateStoreInterface  */
    protected $requestStore;

    /** @var \Symfony\Component\Security\Core\SecurityContextInterface  */
    protected $securityContext;

    /** @var \Symfony\Component\Security\Http\HttpUtils  */
    protected $httpUtils;


    /**
     * @param BindingManager $bindingManager
     * @param RequestStateStoreInterface $requestStore
     * @param \Symfony\Component\Security\Core\SecurityContextInterface $securityContext
     * @param \Symfony\Component\Security\Http\HttpUtils $httpUtils
     */
    public function __construct(
            BindingManager $bindingManager,
            RequestStateStoreInterface $requestStore,
            SecurityContextInterface $securityContext,
            HttpUtils $httpUtils
    ) {
        $this->bindingManager = $bindingManager;
        $this->requestStore = $requestStore;
        $this->securityContext = $securityContext;
        $this->httpUtils = $httpUtils;
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

        /** @var  $logoutResponse LogoutResponse */
        $logoutResponse = $this->bindingManager->receive($request);
        if (!$logoutResponse || !$logoutResponse instanceof LogoutResponse) {
            throw new \InvalidArgumentException('Did not receive logout response');
        }

        $state = $this->requestStore->get($logoutResponse->getID());
        if (!$state) {
            throw new \RuntimeException('Got response to a request that was not made');
        }
        if ($state->getDestination() != $logoutResponse->getIssuer()) {
            throw new \RuntimeException('Got response from different issuer');
        }
        $this->requestStore->remove($state);

        /** @var $token SamlSpToken */
        $token = $this->securityContext->getToken();
        if ($token && $token instanceof SamlSpToken) {
            $samlInfo = $token->getSamlSpInfo();
            if ($samlInfo) {

            }
        }
        return $this->httpUtils->createRedirectResponse($request, $request->attributes->get('local_logout_path'));
    }

} 