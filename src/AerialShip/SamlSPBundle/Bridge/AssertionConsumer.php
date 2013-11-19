<?php

namespace AerialShip\SamlSPBundle\Bridge;

use AerialShip\LightSaml\Binding\BindingDetector;
use AerialShip\LightSaml\Model\Assertion\Assertion;
use AerialShip\LightSaml\Model\Metadata\EntityDescriptor;
use AerialShip\LightSaml\Model\Protocol\Response;
use AerialShip\LightSaml\Model\XmlDSig\SignatureXmlValidator;
use AerialShip\LightSaml\Security\KeyHelper;
use AerialShip\SamlSPBundle\Config\EntityDescriptorProviderInterface;
use AerialShip\SamlSPBundle\RelyingParty\RelyingPartyInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;


class AssertionConsumer implements RelyingPartyInterface
{
    /** @var \AerialShip\SamlSPBundle\Bridge\BindingRequestBuilder  */
    protected $bindingRequestBuilder;

    /** @var  EntityDescriptorProviderInterface */
    protected $idpProvider;



    public function __construct(BindingRequestBuilder $bindingRequestBuilder,
        EntityDescriptorProviderInterface $idpProvider
    ) {
        $this->bindingRequestBuilder = $bindingRequestBuilder;
        $this->idpProvider = $idpProvider;
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

        $detector = new BindingDetector();
        $bindingType = $detector->getBinding($bindingRequest);
        $binding = $detector->instantiate($bindingType);
        /** @var Response $response */
        $response = $binding->receive($bindingRequest);

        $this->validateResponse($response, $request);

        $arr = $response->getAllAssertions();
        if (empty($arr)) {
            throw new \RuntimeException('No assertion received');
        }
        $assertion = $arr[0];
        $nameID = $assertion->getSubject()->getNameID();
        $attributes = $assertion->getAllAttributes();
        $authnStatement = $assertion->getAuthnStatement();

        $result = new SamlSpInfo($nameID, $attributes, $authnStatement);
        return $result;
    }


    protected function validateResponse(Response $response) {
        $this->validateStatus($response);
        $this->validateResponseSignature($response);
        foreach ($response->getAllAssertions() as $assertion) {
            $this->validateAssertion($assertion);
        }
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

    protected function validateResponseSignature(Response $response) {
        /** @var  $signature SignatureXmlValidator */
        if ($signature = $response->getSignature()) {
            $key = $this->getSigningKey();
            if ($key) {
                $signature->validate($key);
            }
        }
    }

    private function validateAssertion(Assertion $assertion) {
        /** @var  $signature SignatureXmlValidator */
        if ($signature = $assertion->getSignature()) {
            $key = $this->getSigningKey();
            if ($key) {
                $signature->validate($key);
            }
        }
    }


    /**
     * @return null|\XMLSecurityKey
     */
    protected function getSigningKey() {
        $result = null;
        $edIDP = $this->idpProvider->getEntityDescriptor();
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