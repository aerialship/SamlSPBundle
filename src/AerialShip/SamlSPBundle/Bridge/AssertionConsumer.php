<?php

namespace AerialShip\SamlSPBundle\Bridge;

use AerialShip\LightSaml\Binding\BindingDetector;
use AerialShip\LightSaml\Model\Assertion\Assertion;
use AerialShip\LightSaml\Model\Protocol\Response;
use AerialShip\LightSaml\Model\XmlDSig\SignatureXmlValidator;
use AerialShip\LightSaml\Security\KeyHelper;
use AerialShip\SamlSPBundle\Config\MetaProvider;
use AerialShip\SamlSPBundle\Config\MetaProviderCollection;
use AerialShip\SamlSPBundle\RelyingParty\RelyingPartyInterface;
use AerialShip\SamlSPBundle\State\Authn\AuthnStateStoreInterface;
use AerialShip\SamlSPBundle\State\SSO\SSOStateStoreInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;


class AssertionConsumer implements RelyingPartyInterface
{
    /** @var BindingRequestBuilder  */
    protected $bindingRequestBuilder;

    /** @var  MetaProviderCollection */
    protected $metaProviders;

    /** @var  AuthnStateStoreInterface */
    protected $authnStore;

    /** @var SSOStateStoreInterface  */
    protected $ssoStore;


    /** @var  BindingDetector */
    protected $bindingDetector;



    public function __construct(BindingRequestBuilder $bindingRequestBuilder,
        MetaProviderCollection $metaProviders,
        AuthnStateStoreInterface $authnStore,
        SSOStateStoreInterface $ssoStore
    ) {
        $this->bindingRequestBuilder = $bindingRequestBuilder;
        $this->metaProviders = $metaProviders;
        $this->authnStore = $authnStore;
        $this->ssoStore = $ssoStore;
        $this->bindingDetector = new BindingDetector();
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

        $bindingRequest = $this->bindingRequestBuilder->getBindingRequest($request);
        $binding = $this->getBinding($bindingRequest);
        /** @var Response $response */
        $response = $binding->receive($bindingRequest);
        if (!$response instanceof Response) {
            throw new \RuntimeException('Expected Protocol/Response type but got '.get_class($response));
        }
        $metaProvider = $this->metaProviders->findByEntityID($response->getIssuer());

        $this->validateResponse($metaProvider, $response, $request);

        $arr = $response->getAllAssertions();
        if (empty($arr)) {
            throw new \RuntimeException('No assertion received');
        }
        $assertion = $arr[0];
        $nameID = $assertion->getSubject()->getNameID();
        $attributes = $assertion->getAllAttributes();
        $authnStatement = $assertion->getAuthnStatement();


        $ssoState = $this->ssoStore->create();
        $ssoState->setNameID($nameID->getValue());
        $ssoState->setNameIDFormat($nameID->getFormat());
        $ssoState->setAuthenticationServiceName($metaProvider->getAuthenticationService());
        $ssoState->setProviderID('saml'); // TODO inject this param to this class
        $ssoState->setSessionIndex($authnStatement->getSessionIndex());
        $this->ssoStore->set($ssoState);

        $result = new SamlSpInfo($metaProvider->getAuthenticationService(), $nameID, $attributes, $authnStatement);
        return $result;
    }


    protected function getBinding(\AerialShip\LightSaml\Binding\Request $bindingRequest) {
        $detector = new BindingDetector();
        $bindingType = $this->bindingDetector->getBinding($bindingRequest);
        $binding = $detector->instantiate($bindingType);
        return $binding;
    }


    protected function validateResponse(MetaProvider $metaProvider, Response $response) {
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

    protected function validateState(Response $response) {
        $authnState = $this->authnStore->get($response->getInResponseTo());
        if (!$authnState) {
            throw new \RuntimeException('Got response to a request that was not made');
        }
        if ($authnState->getDestination() != $response->getIssuer()) {
            throw new \RuntimeException('Got response from different issuer');
        }
        $this->authnStore->remove($authnState);
    }

    protected function validateStatus(Response $response) {
        if (!$response->getStatus()->isSuccess()) {
            $status = $response->getStatus()->getStatusCode()->getValue();
            $status .= "\n".$response->getStatus()->getMessage();
            if ($response->getStatus()->getStatusCode()->getChild()) {
                $status .= "\n".$response->getStatus()->getStatusCode()->getChild()->getValue();
            }
            throw new AuthenticationException('Unsuccessful SAML response: '.$status);
        }
    }

    protected function validateResponseSignature(MetaProvider $metaProvider, Response $response) {
        /** @var  $signature SignatureXmlValidator */
        if ($signature = $response->getSignature()) {
            $key = $this->getSigningKey($metaProvider);
            if ($key) {
                $signature->validate($key);
            }
        }
    }

    private function validateAssertion(MetaProvider $metaProvider, Assertion $assertion) {
        /** @var  $signature SignatureXmlValidator */
        if ($signature = $assertion->getSignature()) {
            $key = $this->getSigningKey($metaProvider);
            if ($key) {
                $signature->validate($key);
            }
        }
        // TODO check notBefore and notOnOrAfter
    }


    /**
     * @param \AerialShip\SamlSPBundle\Config\MetaProvider $metaProvider
     * @return null|\XMLSecurityKey
     */
    protected function getSigningKey(MetaProvider $metaProvider) {
        $result = null;
        $edIDP = $metaProvider->getIdpProvider()->getEntityDescriptor();
        if ($edIDP) {
            $arr = $edIDP->getIdpSsoDescriptors();
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