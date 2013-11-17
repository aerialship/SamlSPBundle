<?php

namespace AerialShip\SamlSPBundle\Bridge;

use AerialShip\LightSaml\Binding\HttpRedirect;
use AerialShip\LightSaml\Meta\AuthnRequestBuilder;
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



    public function __construct(EntityDescriptorProviderInterface $spProvider,
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
    public function supports(Request $request)
    {
        $result = $request->attributes->get('login_path') == $request->getPathInfo();
        return $result;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @throws \InvalidArgumentException if cannot manage the Request
     * @return \Symfony\Component\HttpFoundation\Response|SamlSpResponse
     */
    public function manage(Request $request)
    {
        if (false == $this->supports($request)) {
            throw new \InvalidArgumentException('Unsupported request');
        }



        $spED = $this->spProvider->getEntityDescriptor($request);
        $idpED = $this->idpProvider->getEntityDescriptor($request);
        $spMeta = $this->spMetaProvider->getSpMeta($request);

        $builder = new AuthnRequestBuilder($spED, $idpED, $spMeta);
        $message = $builder->build();

        $binding = new HttpRedirect();
        /** @var \AerialShip\LightSaml\Binding\RedirectResponse $resp */
        $bindingResponse = $binding->send($message);

        $result = new RedirectResponse($bindingResponse->getUrl());
        return $result;
    }

} 