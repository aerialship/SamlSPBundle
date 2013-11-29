<?php

namespace AerialShip\SamlSPBundle\Bridge;

use AerialShip\SamlSPBundle\Config\MetaProviderCollection;
use AerialShip\SamlSPBundle\RelyingParty\RelyingPartyInterface;
use Symfony\Bridge\Twig\TwigEngine;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\HttpUtils;


class Discovery implements RelyingPartyInterface
{
    /** @var  MetaProviderCollection */
    protected $metaProviders;

    /** @var \Symfony\Bridge\Twig\TwigEngine  */
    protected $twig;

    /** @var \Symfony\Component\Security\Http\HttpUtils  */
    protected $httpUtils;


    /**
     * @param string $providerID
     * @param MetaProviderCollection $metaProviders
     * @param TwigEngine $twig
     * @param HttpUtils $httpUtils
     */
    function __construct($providerID, MetaProviderCollection $metaProviders, TwigEngine $twig, HttpUtils $httpUtils) {
        $this->metaProviders = $metaProviders;
        $this->twig = $twig;
        $this->httpUtils = $httpUtils;
    }


    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return bool
     */
    public function supports(Request $request)
    {
        $result = $request->attributes->get('discovery_path') == $request->getPathInfo();
        return $result;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @throws \RuntimeException
     * @throws \InvalidArgumentException if cannot manage the Request
     * @return \Symfony\Component\HttpFoundation\Response|SamlSpInfo
     */
    public function manage(Request $request)
    {
        if (!$this->supports($request)) {
            throw new \InvalidArgumentException('Unsupported request');
        }

        $loginPath = $request->attributes->get('login_path');
        $loginPath = $this->httpUtils->generateUri($request, $loginPath);
        $allProviders = $this->metaProviders->all();

        if (count($allProviders) == 1) {
            // there's only one idp... go straight to it
            $names = array_keys($allProviders);
            return new RedirectResponse($loginPath.'?as='.array_pop($names));
        } else if (count($allProviders) == 0) {
            // configuration validation should ensure this... but anyway just to be sure
            throw new \RuntimeException('At least one authentication service required in configuration');
        } else {
            //$this->metaProviders->get('')->getIdpProvider()->getEntityDescriptor()->getEntityID()
            // present user to choose which idp he wants to authenticate with
            return new Response($this->twig->render(
                '@AerialShipSamlSP/Discovery.html.twig',
                array(
                    'providers' => $this->metaProviders->all(),
                    'login_path' => $loginPath
                )
            ));
        }
    }

}
