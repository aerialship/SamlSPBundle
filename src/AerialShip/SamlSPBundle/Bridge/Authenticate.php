<?php

namespace AerialShip\SamlSPBundle\Bridge;

use AerialShip\SamlSPBundle\RelyingParty\RelyingPartyInterface;
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

        var_dump('Bridge Authenticate');
        exit;
    }

} 