<?php

namespace AerialShip\SamlSPBundle\Bridge;

use AerialShip\LightSaml\Meta\SerializationContext;
use AerialShip\LightSaml\Model\Metadata\KeyDescriptor;
use AerialShip\LightSaml\Model\Metadata\Service\SingleLogoutService;
use AerialShip\LightSaml\Model\Protocol\LogoutRequest;
use AerialShip\LightSaml\Model\Protocol\LogoutResponse;
use AerialShip\LightSaml\Model\Protocol\Status;
use AerialShip\LightSaml\Model\XmlDSig\SignatureValidatorInterface;
use AerialShip\LightSaml\Security\KeyHelper;
use AerialShip\SamlSPBundle\Config\ServiceInfo;
use AerialShip\SamlSPBundle\Config\ServiceInfoCollection;
use AerialShip\SamlSPBundle\RelyingParty\RelyingPartyInterface;
use AerialShip\SamlSPBundle\State\SSO\SSOState;
use AerialShip\SamlSPBundle\State\SSO\SSOStateStoreInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class LogoutReceiveRequest implements RelyingPartyInterface
{
    /** @var BindingManager */
    protected $bindingManager;

    /** @var SSOStateStoreInterface  */
    protected $ssoStore;

    /** @var ServiceInfoCollection  */
    protected $serviceInfoCollection;



    public function __construct(BindingManager $bindingManager, SSOStateStoreInterface $ssoStore, ServiceInfoCollection $serviceInfoCollection)
    {
        $this->bindingManager = $bindingManager;
        $this->ssoStore = $ssoStore;
        $this->serviceInfoCollection = $serviceInfoCollection;
    }


    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return bool
     */
    public function supports(Request $request) {
        if ($request->attributes->get('logout_path') != $request->getPathInfo()) {
            return false;
        }
        if (!$request->get('SAMLRequest')) {
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

        $logoutRequest = $this->receiveRequest($request);
        $serviceInfo = $this->getServiceInfo($logoutRequest);
        $this->validateLogoutRequest($serviceInfo, $logoutRequest);
        $arrStates = $this->getSSOState($serviceInfo, $logoutRequest);
        $this->deleteSSOState($arrStates);

        $logoutResponse = new LogoutResponse();
        $logoutResponse->setIssuer($serviceInfo->getSpProvider()->getEntityDescriptor()->getEntityID());
        $logoutResponse->setInResponseTo($logoutRequest->getID());

        $arrSLO = $serviceInfo->getIdpProvider()->getEntityDescriptor()->getFirstIdpSsoDescriptor()->findSingleLogoutServices();
        /** @var  $slo SingleLogoutService */
        $slo = array_pop($arrSLO);
        $logoutResponse->setDestination($slo->getLocation());

        $status = new Status();
        $status->setSuccess();
        $logoutResponse->setStatus($status);

        $context = new SerializationContext();
        $logoutResponse->getXml($context->getDocument(), $context);
        $xml = $context->getDocument()->saveXML();

        return new Response($xml, 200, array('Content-Type', 'application/xml'));
    }


    /**
     * @param Request $request
     * @return LogoutRequest
     * @throws \InvalidArgumentException
     */
    protected function receiveRequest(Request $request)
    {
        /** @var  $logoutRequest LogoutRequest */
        $logoutRequest = $this->bindingManager->receive($request);
        if (!$logoutRequest || !$logoutRequest instanceof LogoutRequest) {
            throw new \InvalidArgumentException('Did not receive logout request');
        }

        return $logoutRequest;
    }


    /**
     * @param LogoutRequest $logoutRequest
     * @return ServiceInfo|null
     * @throws \RuntimeException
     */
    protected function getServiceInfo(LogoutRequest $logoutRequest)
    {
        $serviceInfo = $this->serviceInfoCollection->findByIDPEntityID($logoutRequest->getIssuer());
        if (!$serviceInfo) {
            throw new \RuntimeException('Got logout request from unknown IDP: '.$logoutRequest->getIssuer());
        }

        return $serviceInfo;
    }

    /**
     * @param \AerialShip\SamlSPBundle\Config\ServiceInfo $serviceInfo
     * @param LogoutRequest $logoutRequest
     * @throws \RuntimeException
     */
    protected function validateLogoutRequest(ServiceInfo $serviceInfo, LogoutRequest $logoutRequest)
    {
        $idp = $serviceInfo->getIdpProvider()->getEntityDescriptor();
        $keyDescriptors = $idp->getFirstIdpSsoDescriptor()->findKeyDescriptors('signing');
        if (empty($keyDescriptors)) {
            throw new \RuntimeException('IDP must support signing for logout requests');
        }
        /** @var  $kd KeyDescriptor */
        $kd = array_pop($keyDescriptors);

        /** @var  $signature SignatureValidatorInterface */
        $signature = $logoutRequest->getSignature();
        if (!$signature) {
            throw new \RuntimeException('Logout request must be signed');
        }
        $key = KeyHelper::createPublicKey($kd->getCertificate());
        $signature->validate($key);
    }


    /**
     * @param ServiceInfo $serviceInfo
     * @param LogoutRequest $logoutRequest
     * @return SSOState[]
     */
    protected function getSSOState(ServiceInfo $serviceInfo, LogoutRequest $logoutRequest)
    {
        if ($logoutRequest->getSessionIndex()) {
            $result = array();
            $state = $this->ssoStore->getOneByNameIDSessionIndex(
                $serviceInfo->getProviderID(),
                $serviceInfo->getAuthenticationService(),
                $logoutRequest->getNameID()->getValue(),
                $logoutRequest->getSessionIndex()
            );
            if ($state) {
                $result[] = $state;
            }
        } else {
            $result = $this->ssoStore->getAllByNameID(
                $serviceInfo->getProviderID(),
                $serviceInfo->getAuthenticationService(),
                $logoutRequest->getNameID()->getValue()
            );
        }
        return $result;
    }


    protected function deleteSSOState(array $arrStates)
    {
        foreach ($arrStates as $state) {
            $this->ssoStore->remove($state);
        }
    }
} 