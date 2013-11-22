<?php

namespace AerialShip\SamlSPBundle\Bridge;

use AerialShip\LightSaml\Meta\SerializationContext;
use AerialShip\SamlSPBundle\Config\MetaProviderCollection;
use AerialShip\SamlSPBundle\Config\SpEntityDescriptorBuilder;
use AerialShip\SamlSPBundle\RelyingParty\RelyingPartyInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Security\Http\HttpUtils;


class FederationMetadata implements RelyingPartyInterface
{
    /** @var \AerialShip\SamlSPBundle\Config\SpEntityDescriptorBuilder  */
    protected $sp;


    /**
     * @param SpEntityDescriptorBuilder $sp
     */
    function __construct(SpEntityDescriptorBuilder $sp) {
        $this->sp = $sp;
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
        $this->sp->setRequest($request);
        $ed = $this->sp->getEntityDescriptor();
        $context = new SerializationContext();
        $ed->getXml($context->getDocument(), $context);
        $result = new Response($context->getDocument()->saveXML());
        $result->headers->set('Content-Type', 'application/xml');
        return $result;
    }

}
