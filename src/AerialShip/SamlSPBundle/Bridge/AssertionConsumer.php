<?php

namespace AerialShip\SamlSPBundle\Bridge;

use AerialShip\LightSaml\Bindings;
use AerialShip\LightSaml\Model\Assertion\Assertion;
use AerialShip\LightSaml\Model\Protocol\Response;
use AerialShip\LightSaml\Model\XmlDSig\SignatureXmlValidator;
use AerialShip\LightSaml\Security\KeyHelper;
use AerialShip\SamlSPBundle\Config\ServiceInfo;
use AerialShip\SamlSPBundle\Config\ServiceInfoCollection;
use AerialShip\SamlSPBundle\RelyingParty\RelyingPartyInterface;
use AerialShip\SamlSPBundle\State\Request\RequestStateStoreInterface;
use AerialShip\SamlSPBundle\State\SSO\SSOStateStoreInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AssertionConsumer implements RelyingPartyInterface
{
    /** @var BindingManager  */
    protected $bindingManager;

    /** @var  ServiceInfoCollection */
    protected $serviceInfoCollection;

    /** @var  RequestStateStoreInterface */
    protected $requestStore;

    /** @var SSOStateStoreInterface  */
    protected $ssoStore;




    public function __construct(
        BindingManager $bindingManager,
        ServiceInfoCollection $serviceInfoCollection,
        RequestStateStoreInterface $requestStore,
        SSOStateStoreInterface $ssoStore
    ) {
        $this->bindingManager = $bindingManager;
        $this->serviceInfoCollection = $serviceInfoCollection;
        $this->requestStore = $requestStore;
        $this->ssoStore = $ssoStore;
    }



    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return bool
     */
    public function supports(Request $request)
    {
        $result = $request->attributes->get('check_path') == $request->getPathInfo();
        return $result;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @throws \RuntimeException
     * @throws \Symfony\Component\Security\Core\Exception\AuthenticationException
     * @throws \InvalidArgumentException if cannot manage the Request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|SamlSpInfo
     */
    public function manage(Request $request)
    {
        if (!$this->supports($request)) {
            throw new \InvalidArgumentException();
        }

        $response = $this->getSamlResponse($request);
        $serviceInfo = $this->serviceInfoCollection->findByIDPEntityID($response->getIssuer());

        $serviceInfo->getSpProvider()->setRequest($request);
        $this->validateResponse($serviceInfo, $response);

        $assertion = $this->getSingleAssertion($response);

        $this->createSSOState($serviceInfo, $assertion);

        return new SamlSpInfo(
            $serviceInfo->getAuthenticationService(),
            $assertion->getSubject()->getNameID(),
            $assertion->getAllAttributes(),
            $assertion->getAuthnStatement()
        );
    }


    protected function getSamlResponse(Request $request)
    {
        $bindingType = null;
        /** @var Response $response */
        $response = $this->bindingManager->receive($request, $bindingType);
        if ($bindingType == Bindings::SAML2_HTTP_REDIRECT) {
            throw new \RuntimeException('SAML protocol response cannot be sent via binding HTTP REDIRECT');
        }
        if (!$response instanceof Response) {
            throw new \RuntimeException('Expected Protocol/Response type but got '.($response ? get_class($response) : 'nothing'));
        }

        return $response;
    }

    /**
     * @param Response $response
     * @return Assertion
     * @throws \RuntimeException
     */
    protected function getSingleAssertion(Response $response)
    {
        $arr = $response->getAllAssertions();
        if (empty($arr)) {
            throw new \RuntimeException('No assertion received');
        }
        $assertion = array_pop($arr);

        return $assertion;
    }


    protected function createSSOState(ServiceInfo $serviceInfo, Assertion $assertion)
    {
        $ssoState = $this->ssoStore->create();
        $ssoState->setNameID($assertion->getSubject()->getNameID()->getValue());
        $ssoState->setNameIDFormat($assertion->getSubject()->getNameID()->getFormat() ?: '');
        $ssoState->setAuthenticationServiceName($serviceInfo->getAuthenticationService());
        $ssoState->setProviderID($serviceInfo->getProviderID());
        $ssoState->setSessionIndex($assertion->getAuthnStatement()->getSessionIndex());
        $this->ssoStore->set($ssoState);

        return $ssoState;
    }


    protected function validateResponse(ServiceInfo $metaProvider, Response $response)
    {
        if (!$metaProvider) {
            throw new \RuntimeException('Unknown issuer '.$response->getIssuer());
        }
        $this->validateState($response);
        $this->validateStatus($response);
        $this->validateResponseSignature($metaProvider, $response);
        foreach ($response->getAllAssertions() as $assertion) {
            $this->validateAssertion($metaProvider, $assertion);
        }
    }

    protected function validateState(Response $response)
    {
        if ($response->getInResponseTo()) {
            $requestState = $this->requestStore->get($response->getInResponseTo());
            if (!$requestState) {
                throw new \RuntimeException('Got response to a request that was not made');
            }
            if ($requestState->getDestination() != $response->getIssuer()) {
                throw new \RuntimeException('Got response from different issuer');
            }
            $this->requestStore->remove($requestState);
        }
    }

    protected function validateStatus(Response $response)
    {
        if (!$response->getStatus()->isSuccess()) {
            $status = $response->getStatus()->getStatusCode()->getValue();
            $status .= "\n".$response->getStatus()->getMessage();
            if ($response->getStatus()->getStatusCode()->getChild()) {
                $status .= "\n".$response->getStatus()->getStatusCode()->getChild()->getValue();
            }
            throw new AuthenticationException('Unsuccessful SAML response: '.$status);
        }
    }

    protected function validateResponseSignature(ServiceInfo $serviceInfo, Response $response)
    {
        /** @var  $signature SignatureXmlValidator */
        if ($signature = $response->getSignature()) {
            $keys = $this->getAllKeys($serviceInfo);
            $signature->validateMulti($keys);
        }
    }

    protected function validateAssertion(ServiceInfo $serviceInfo, Assertion $assertion)
    {
        $this->validateAssertionSignature($assertion, $serviceInfo);
        $this->validateAssertionTime($assertion);
        $this->validateAssertionSubjectTime($assertion);
        $this->validateSubjectConfirmationRecipient($assertion, $serviceInfo);
    }

    protected function validateAssertionSignature(Assertion $assertion, ServiceInfo $serviceInfo)
    {
        /** @var  $signature SignatureXmlValidator */
        if ($signature = $assertion->getSignature()) {
            $keys = $this->getAllKeys($serviceInfo);
            $signature->validateMulti($keys);
        } else {
            throw new AuthenticationException('Assertion must be signed');
        }
    }


    /**
     * @param Assertion $assertion
     * @throws \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    protected function validateAssertionTime(Assertion $assertion)
    {
        if ($assertion->getNotBefore() && $assertion->getNotBefore() > time() + 60) {
            throw new AuthenticationException('Received an assertion that is valid in the future. Check clock synchronization on IdP and SP');
        }
        if ($assertion->getNotOnOrAfter() && $assertion->getNotOnOrAfter() <= time() - 60) {
            throw new AuthenticationException('Received an assertion that has expired. Check clock synchronization on IdP and SP');
        }
    }

    /**
     * @param Assertion $assertion
     * @throws \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    protected function validateAssertionSubjectTime(Assertion $assertion)
    {
        $arrSubjectConfirmations = $assertion->getSubject()->getSubjectConfirmations();
        if ($arrSubjectConfirmations) {
            foreach ($arrSubjectConfirmations as $subjectConfirmation) {
                if ($data = $subjectConfirmation->getData()) {
                    if ($data->getNotBefore() && $data->getNotBefore() > time() + 60) {
                        throw new AuthenticationException('Received an assertion with a session valid in future. Check clock synchronization on IdP and SP');
                    }
                    if ($data->getNotOnOrAfter() && $data->getNotOnOrAfter() <= time() - 60) {
                        throw new AuthenticationException('Received an assertion with a session that has expired. Check clock synchronization on IdP and SP');
                    }
                }
            }
        }
    }


    protected function validateSubjectConfirmationRecipient(Assertion $assertion, ServiceInfo $serviceInfo)
    {
        $arrACS = $serviceInfo->getSpProvider()
                ->getEntityDescriptor()
                ->getFirstSpSsoDescriptor()
                ->findAssertionConsumerServices();
        foreach ($assertion->getSubject()->getSubjectConfirmations() as $subjectConfirmation) {
            $ok = false;
            foreach ($arrACS as $acs) {
                if ($acs->getLocation() == $subjectConfirmation->getData()->getRecipient()) {
                    $ok = true;
                    break;
                }
            }
            if (!$ok) {
                throw new AuthenticationException(
                    sprintf(
                        'Invalid Assertion SubjectConfirmation Recipient %s',
                        $subjectConfirmation->getData()->getRecipient()
                    )
                );
            }
        }
    }

    /**
     * @param ServiceInfo $metaProvider
     * @return \XMLSecurityKey[]
     */
    protected function getAllKeys(ServiceInfo $metaProvider)
    {
        $result = array();
        $edIDP = $metaProvider->getIdpProvider()->getEntityDescriptor();
        if ($edIDP) {
            $arr = $edIDP->getAllIdpSsoDescriptors();
            if ($arr) {
                $idp = $arr[0];
                $keyDescriptors = $idp->getKeyDescriptors();
                foreach ($keyDescriptors as $keyDescriptor) {
                    $certificate = $keyDescriptor->getCertificate();
                    $result[] = KeyHelper::createPublicKey($certificate);
                }
            }
        }

        return $result;
    }

    /**
     * @param \AerialShip\SamlSPBundle\Config\ServiceInfo $metaProvider
     * @return null|\XMLSecurityKey
     */
    protected function getSigningKey(ServiceInfo $metaProvider)
    {
        $result = null;
        $edIDP = $metaProvider->getIdpProvider()->getEntityDescriptor();
        if ($edIDP) {
            $arr = $edIDP->getAllIdpSsoDescriptors();
            if ($arr) {
                $idp = $arr[0];
                $arr = $idp->findKeyDescriptors('signing');
                if ($arr) {
                    $keyDescriptor = $arr[0];
                    $certificate = $keyDescriptor->getCertificate();
                    $result = KeyHelper::createPublicKey($certificate);
                }
            }
        }
        
        return $result;
    }
}
