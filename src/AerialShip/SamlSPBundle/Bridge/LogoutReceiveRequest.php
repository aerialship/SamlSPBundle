<?php

namespace AerialShip\SamlSPBundle\Bridge;

use AerialShip\LightSaml\Helper;
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
use AerialShip\SamlSPBundle\State\SSO\SSOStateStoreInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\HttpUtils;

class LogoutReceiveRequest extends LogoutBase implements RelyingPartyInterface
{
    /** @var BindingManager */
    protected $bindingManager;

    /** @var ServiceInfoCollection  */
    protected $serviceInfoCollection;



    public function __construct(
        BindingManager $bindingManager,
        SSOStateStoreInterface $ssoStore,
        ServiceInfoCollection $serviceInfoCollection,
        HttpUtils $httpUtils
    ) {
        parent::__construct($ssoStore, $httpUtils);
        $this->bindingManager = $bindingManager;
        $this->serviceInfoCollection = $serviceInfoCollection;
    }


    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return bool
     */
    public function supports(Request $request)
    {
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
    public function manage(Request $request)
    {
        if (!$this->supports($request)) {
            throw new \InvalidArgumentException('Unsupported request');
        }

        $logoutRequest = $this->receiveRequest($request);
        $serviceInfo = $this->getServiceInfo($logoutRequest);
        $this->validateLogoutRequest($serviceInfo, $logoutRequest);
        $arrStates = $this->getSSOState($serviceInfo, $logoutRequest->getNameID()->getValue(), $logoutRequest->getSessionIndex());
        $this->deleteSSOState($arrStates);

        $logoutResponse = new LogoutResponse();
        $logoutResponse->setID(Helper::generateID());
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

        return new Response($xml, 200, array('Content-Type' => 'application/xml'));
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
        $keyDescriptors = $idp->getFirstIdpSsoDescriptor()->getKeyDescriptors();
        if (empty($keyDescriptors)) {
            throw new \RuntimeException('IDP must support signing for logout requests');
        }

        /** @var  $signature SignatureValidatorInterface */
        $signature = $logoutRequest->getSignature();
        if (!$signature) {
            throw new \RuntimeException('Logout request must be signed');
        }

        $keys = array();
        foreach ($keyDescriptors as $keyDescriptor) {
            $key = KeyHelper::createPublicKey($keyDescriptor->getCertificate());
            $keys[] = $key;
        }

        $signature->validateMulti($keys);
    }
}
