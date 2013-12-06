<?php

namespace AerialShip\SamlSPBundle\Bridge;

use AerialShip\LightSaml\Binding\HttpRedirect;
use AerialShip\LightSaml\Meta\LogoutRequestBuilder;
use AerialShip\SamlSPBundle\Config\MetaProviderCollection;
use AerialShip\SamlSPBundle\Config\SpEntityDescriptorBuilder;
use AerialShip\SamlSPBundle\RelyingParty\RelyingPartyInterface;
use AerialShip\SamlSPBundle\Security\Core\Token\SamlSpToken;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContextInterface;


class Logout implements RelyingPartyInterface
{
    /** @var \Symfony\Component\Security\Core\SecurityContextInterface  */
    protected $securityContext;

    /** @var \AerialShip\SamlSPBundle\Config\SpEntityDescriptorBuilder  */
    protected $sp;

    /** @var  MetaProviderCollection */
    protected $metaProviders;



    public function __construct(SecurityContextInterface $securityContext, SpEntityDescriptorBuilder $sp, MetaProviderCollection $metaProviders)
    {
        $this->securityContext = $securityContext;
        $this->sp = $sp;
        $this->metaProviders = $metaProviders;
    }


    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return bool
     */
    function supports(Request $request) {
        if ($request->attributes->get('logout_path') != $request->getPathInfo()) {
            return false;
        }
        /** @var $token SamlSpToken */
        $token = $this->securityContext->getToken();
        if (!$token || !$token instanceof SamlSpToken) {
            return false;
        }
        if (!$token->getSamlSpInfo()) {
            return false;
        }
        return true;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @throws \InvalidArgumentException if cannot manage the Request
     * @return \Symfony\Component\HttpFoundation\Response|SamlSpInfo
     */
    function manage(Request $request) {
        if (!$this->supports($request)) {
            throw new \InvalidArgumentException('Unsupported request');
        }

        /** @var $token SamlSpToken */
        $token = $this->securityContext->getToken();
        $samlInfo = $token->getSamlSpInfo();

        $metaProvider = $this->metaProviders->get($samlInfo->getAuthenticationServiceID());
        $this->sp->setRequest($request);

        $builder = new LogoutRequestBuilder(
            $this->sp->getEntityDescriptor(),
            $metaProvider->getIdpProvider()->getEntityDescriptor(),
            $metaProvider->getSpMetaProvider()->getSpMeta()
        );

        $logoutRequest = $builder->build(
            $samlInfo->getNameID()->getFormat(),
            $samlInfo->getNameID()->getValue()
        );

        $binding = new HttpRedirect();
        $bindingResponse = $binding->send($logoutRequest);

        return new Response($bindingResponse->getUrl());
        return new RedirectResponse($bindingResponse->getUrl());
    }

} 