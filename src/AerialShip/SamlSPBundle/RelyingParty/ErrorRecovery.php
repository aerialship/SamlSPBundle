<?php

namespace AerialShip\SamlSPBundle\RelyingParty;


use AerialShip\SamlSPBundle\Bridge\SamlSpResponse;
use AerialShip\SamlSPBundle\Security\Token\SamlSpToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;

class ErrorRecovery implements RelyingPartyInterface
{
    const RECOVERED_QUERY_PARAMETER = 'saml_failure_recovered';

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return bool
     */
    function supports(Request $request) {
        if (false == $request->get(self::RECOVERED_QUERY_PARAMETER)) {
            return false;
        }
        if (false == $error = $request->getSession()->get(SecurityContextInterface::AUTHENTICATION_ERROR)) {
            return false;
        }
        if (false == $error->getToken() instanceof SamlSpToken) {
            return false;
        }
        return true;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @throws \InvalidArgumentException if cannot manage the Request
     * @return \Symfony\Component\HttpFoundation\Response|SamlSpResponse
     */
    function manage(Request $request) {
        /** @var $error AuthenticationException */
        $error = $request->getSession()->get(SecurityContextInterface::AUTHENTICATION_ERROR);
        $request->getSession()->remove(SecurityContextInterface::AUTHENTICATION_ERROR);
        /** @var $token SamlSpToken */
        $token = $error->getToken();
        return new SamlSpResponse(
            $token->getNameID(),
            $token->getAttributes()
        );
    }

} 