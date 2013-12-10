<?php

namespace AerialShip\SamlSPBundle\Bridge;

use AerialShip\SamlSPBundle\RelyingParty\RelyingPartyInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\HttpUtils;

class LogoutFallback implements RelyingPartyInterface
{
    /** @var \Symfony\Component\Security\Http\HttpUtils  */
    protected $httpUtils;



    public function __construct(HttpUtils $httpUtils)
    {
        $this->httpUtils = $httpUtils;
    }



    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return bool
     */
    public function supports(Request $request)
    {
        if ($request->attributes->get('logout_path') == $request->getPathInfo()) {
            return true;
        }
        return false;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @throws \InvalidArgumentException if cannot manage the Request
     * @return \Symfony\Component\HttpFoundation\Response|SamlSpInfo|null
     */
    public function manage(Request $request)
    {
        return $this->httpUtils->createRedirectResponse($request, $request->attributes->get('local_logout_path'));
    }

} 