<?php

namespace AerialShip\SamlSPBundle\Bridge;

use AerialShip\LightSaml\Binding\HttpRedirect;
use AerialShip\LightSaml\Bindings;
use AerialShip\LightSaml\Meta\AuthnRequestBuilder;
use AerialShip\LightSaml\Meta\SpMeta;
use AerialShip\LightSaml\Model\Metadata\EntityDescriptor;
use AerialShip\LightSaml\NameIDPolicy;
use AerialShip\SamlSPBundle\Config\EntityDescriptorProviderInterface;
use AerialShip\SamlSPBundle\Config\SpMetaProviderInterface;
use AerialShip\SamlSPBundle\RelyingParty\RelyingPartyInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;


class Authenticate implements RelyingPartyInterface
{
    /** @var  EntityDescriptorProviderInterface */
    protected $spProvider;

    /** @var  EntityDescriptorProviderInterface */
    protected $idpProvider;

    /** @var  SpMetaProviderInterface */
    protected $spMetaProvider;



    function __construct(EntityDescriptorProviderInterface $spProvider,
        EntityDescriptorProviderInterface $idpProvider,
        SpMetaProviderInterface $spMetaProvider
    ) {
        $this->spProvider = $spProvider;
        $this->idpProvider = $idpProvider;
        $this->spMetaProvider = $spMetaProvider;
    }



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
     * @return \Symfony\Component\HttpFoundation\Response|SamlSpResponse
     */
    function manage(Request $request) {
        if (false == $this->supports($request)) {
            throw new \InvalidArgumentException('Unsupported request');
        }

        $spED = $this->spProvider->getEntityDescriptor($request);
        $idpED = $this->idpProvider->getEntityDescriptor($request);
        $spMeta = $this->spMetaProvider->getSpMeta($request);

        $builder = new AuthnRequestBuilder($spED, $idpED, $spMeta);
        $req = $builder->build();

        $binding = new HttpRedirect();
        /** @var \AerialShip\LightSaml\Binding\RedirectResponse $resp */
        $resp = $binding->send($req);

        $result = new RedirectResponse($resp->getUrl());
        return $result;
    }

} 