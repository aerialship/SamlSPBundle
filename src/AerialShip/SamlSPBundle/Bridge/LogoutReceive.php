<?php

namespace AerialShip\SamlSPBundle\Bridge;

use AerialShip\SamlSPBundle\RelyingParty\RelyingPartyInterface;
use Symfony\Component\HttpFoundation\Request;


class LogoutReceive implements RelyingPartyInterface
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return bool
     */
    function supports(Request $request) {
        $result = $request->attributes->get('logout_receive_path') == $request->getPathInfo();
        return $result;
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

        // TODO receive logout request, drop sso state from store, and send logout response back to idp
    }

}
