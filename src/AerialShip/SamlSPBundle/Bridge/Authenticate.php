<?php

namespace AerialShip\SamlSPBundle\Bridge;

use AerialShip\LightSaml\Binding\HttpRedirect;
use AerialShip\LightSaml\Bindings;
use AerialShip\LightSaml\Meta\AuthnRequestBuilder;
use AerialShip\LightSaml\Meta\SpMeta;
use AerialShip\LightSaml\Model\Metadata\EntityDescriptor;
use AerialShip\LightSaml\NameIDPolicy;
use AerialShip\SamlSPBundle\RelyingParty\RelyingPartyInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;


class Authenticate implements RelyingPartyInterface
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return bool
     */
    function supports(Request $request) {
        $result = $request->attributes->get('login_path') == $request->getPathInfo();
        return $result;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @throws \InvalidArgumentException if cannot manage the Request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|SamlSpResponse
     */
    function manage(Request $request) {
        if (false == $this->supports($request)) {
            throw new \InvalidArgumentException('Unsupported request');
        }

        $spED = new EntityDescriptor();
        $doc = new \DOMDocument();
        $doc->load('d:\www\home\aerial\test\src\AerialShip\SamlTestBundle\Resources\sp.xml');
        $spED->loadFromXml($doc->firstChild);

        $idpED = new EntityDescriptor();
        $doc = new \DOMDocument();
        $doc->load('d:\www\home\aerial\test\vendor\aerialship\lightsaml\resources\sample\EntityDescriptor\idp2-ed.xml');
        $idpED->loadFromXml($doc->firstChild);

        $spMeta = new SpMeta();
        $spMeta->setNameIdFormat(NameIDPolicy::TRANSIENT);
        $spMeta->setAuthnRequestBinding(Bindings::SAML2_HTTP_REDIRECT);

        $builder = new AuthnRequestBuilder($spED, $idpED, $spMeta);
        $req = $builder->build();

        $binding = new HttpRedirect();
        /** @var \AerialShip\LightSaml\Binding\RedirectResponse $resp */
        $resp = $binding->send($req);

        $result = new RedirectResponse($resp->getUrl());
        return $result;
    }

} 