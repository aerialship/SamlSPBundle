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
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;


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
     * @throws \InvalidArgumentException if cannot manage the Request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|SamlSpResponse
     */
    public function manage(Request $request)
    {
        $bindingRequest = $this->bindingRequestBuilder->getBindingRequest($request);

        $detector = new BindingDetector();
        $bindingType = $detector->getBinding($bindingRequest);
        $binding = $detector->instantiate($bindingType);
        /** @var Response $response */
        $response = $binding->receive($bindingRequest);

        if (!$response->getStatus()->isSuccess()) {
            $status = $response->getStatus()->getStatusCode()->getValue();
            $status .= ' '.$response->getStatus()->getMessage();
            if ($response->getStatus()->getStatusCode()->getChild()) {
                $status .= ' '.$response->getStatus()->getStatusCode()->getChild()->getValue();
            }
            throw new AuthenticationException('Unsuccessful SAML response: '.$status);
        }

        $assertion = $response->getAllAssertions()[0];
        $nameID = $assertion->getSubject()->getNameID();
        $attributes = $assertion->getAllAttributes();

        $result = new SamlSpResponse($nameID, $attributes);
        return $result;
    }


    protected function validateResponse(Response $response, Request $request) {
        $edIDP = $this->idpProvider->getEntityDescriptor($request);
        if ($edIDP) {
            $this->validateResponseSignature($response, $edIDP);
        }
        foreach ($response->getAllAssertions() as $assertion) {
            $this->validateAssertion($assertion);
        }
    }

    protected function validateResponseSignature(Response $response, EntityDescriptor $edIDP) {
        if ($signature = $response->getSignature()) {
            if ($edIDP) {
                $arr = $edIDP->getIdpSsoDescriptors();
                if ($arr) {
                    $idp = $arr[0];
                    $arr = $idp->findKeyDescriptors('signing');
                    if ($arr) {
                        $keyDescriptor = $arr[0];
                        $certificate = $keyDescriptor->getCertificate();
                        $key = KeyHelper::createPublicKey($certificate);
                        /** @var $signature SignatureXmlValidator */
                        $signature->validate($key);
                    }
                }
            }
        }
    }

    private function validateAssertion(Assertion $assertion) {

    }


}