<?php

namespace AerialShip\SamlSPBundle\Bridge;

use AerialShip\SamlSPBundle\Config\ServiceInfoCollection;
use AerialShip\SamlSPBundle\RelyingParty\RelyingPartyInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\HttpUtils;

class Discovery implements RelyingPartyInterface
{
    /** @var  ServiceInfoCollection */
    protected $metaProviders;

    /** @var \Symfony\Component\Templating\EngineInterface  */
    protected $twig;

    /** @var \Symfony\Component\Security\Http\HttpUtils  */
    protected $httpUtils;


    /**
     * @param string $providerID
     * @param ServiceInfoCollection $metaProviders
     * @param EngineInterface $twig
     * @param HttpUtils $httpUtils
     */
    function __construct($providerID, ServiceInfoCollection $metaProviders, EngineInterface  $twig, HttpUtils $httpUtils)
    {
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

        $path = $this->getPath($request);

        $allProviders = $this->metaProviders->all();

        if (count($allProviders) == 1) {
            // there's only one idp... go straight to it
            $names = array_keys($allProviders);
            return new RedirectResponse($path.'?as='.array_pop($names));
        } else if (count($allProviders) == 0) {
            // configuration validation should ensure this... but anyway just to be sure
            throw new \RuntimeException('At least one authentication service required in configuration');
        } else {
            //$this->metaProviders->get('')->getIdpProvider()->getEntityDescriptor()->getEntityID()
            // present user to choose which idp he wants to authenticate with
            return new Response($this->twig->render(
                '@AerialShipSamlSP/discovery.html.twig',
                array(
                    'providers' => $this->metaProviders->all(),
                    'path' => $path
                )
            ));
        }
    }


    protected function getPath(Request $request)
    {
        $type = $request->query->get('type');
        switch ($type) {
            case 'metadata':
                $path = $request->attributes->get('metadata_path');
                break;
            case 'logout':
                $path = $request->attributes->get('logout_path');
                break;
            default:
                $path = $request->attributes->get('login_path');
        }
        $path = $this->httpUtils->generateUri($request, $path);
        return $path;
    }
}
