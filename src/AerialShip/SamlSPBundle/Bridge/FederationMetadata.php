<?php

namespace AerialShip\SamlSPBundle\Bridge;

use AerialShip\LightSaml\Meta\SerializationContext;
use AerialShip\SamlSPBundle\Config\ServiceInfoCollection;
use AerialShip\SamlSPBundle\RelyingParty\RelyingPartyInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\HttpUtils;


class FederationMetadata implements RelyingPartyInterface
{
    /** @var \AerialShip\SamlSPBundle\Config\ServiceInfoCollection */
    protected $serviceInfoCollection;

    /** @var \Symfony\Component\Security\Http\HttpUtils  */
    protected $httpUtils;



    /**
     * @param ServiceInfoCollection $serviceInfoCollection
     * @param \Symfony\Component\Security\Http\HttpUtils $httpUtils
     */
    public function __construct(ServiceInfoCollection $serviceInfoCollection, HttpUtils $httpUtils)
    {
        $this->serviceInfoCollection = $serviceInfoCollection;
        $this->httpUtils = $httpUtils;
    }


    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return bool
     */
    function supports(Request $request) {
        $result = $request->attributes->get('metadata_path') == $request->getPathInfo();
        return $result;
    }


    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return \Symfony\Component\HttpFoundation\Response|SamlSpInfo
     */
    function manage(Request $request) {
        $serviceInfo = $this->serviceInfoCollection->findByAS($request->query->get('as'));
        if (!$serviceInfo) {
            return $this->httpUtils->createRedirectResponse($request, $request->attributes->get('discovery_path').'?type=metadata');
        }

        $serviceInfo->getSpProvider()->setRequest($request);
        $ed = $serviceInfo->getSpProvider()->getEntityDescriptor();

        $context = new SerializationContext();
        $ed->getXml($context->getDocument(), $context);
        $result = new Response($context->getDocument()->saveXML());
        $result->headers->set('Content-Type', 'application/samlmetadata+xml');
        return $result;
    }

}
