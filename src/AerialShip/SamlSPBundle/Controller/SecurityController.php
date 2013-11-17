<?php

namespace AerialShip\SamlSPBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;

class SecurityController extends Controller
{
    function loginAction() {
        throw new \RuntimeException('You must configure the login path to be handled by the firewall using bwc_saml_sp in your security firewall configuration.');
    }

    function checkAction() {
        throw new \RuntimeException('You must configure the check path to be handled by the firewall using bwc_saml_sp in your security firewall configuration.');
    }

    function logoutAction() {
        throw new \RuntimeException('You must activate the logout in your security firewall configuration.');
    }

    function failureAction() {
        /** @var $error AuthenticationException */
        $error = $this->getRequest()->getSession()->get(SecurityContextInterface::AUTHENTICATION_ERROR);
        $this->getRequest()->getSession()->remove(SecurityContextInterface::AUTHENTICATION_ERROR);
        print "<pre>\n";
        print $error->getMessage();
        print "<hr/>\n";
        print $error->getTraceAsString();
        exit;
    }

}