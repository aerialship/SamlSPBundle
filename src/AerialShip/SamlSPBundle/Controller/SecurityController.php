<?php

namespace AerialShip\SamlSPBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;

class SecurityController extends Controller
{
    function loginAction() {
        throw new \RuntimeException('You must configure the login path to be handled by the firewall using aerial_ship_saml_sp in your security firewall configuration.');
    }

    function acsAction() {
        throw new \RuntimeException('You must configure the assertion consumer path path to be handled by the firewall using aerial_ship_saml_sp in your security firewall configuration.');
    }

    function logoutAction() {
        throw new \RuntimeException('You must activate the logout in your security firewall configuration.');
    }

    function logoutReceiveAction() {
        throw new \RuntimeException('You must configure the logout receive path path to be handled by the firewall using aerial_ship_saml_sp in your security firewall configuration.');
    }

    function federationMetadataAction() {
        throw new \RuntimeException('You must configure the federation metadata path path to be handled by the firewall using aerial_ship_saml_sp in your security firewall configuration.');
    }

    function discoveryAction() {
        throw new \RuntimeException('You must configure the discovery path path to be handled by the firewall using aerial_ship_saml_sp in your security firewall configuration.');
    }

    function failureAction() {
        /** @var $error AuthenticationException */
        $error = $this->getRequest()->getSession()->get(SecurityContextInterface::AUTHENTICATION_ERROR);
        return $this->render('AerialShipSamlSPBundle::failure.html.twig', array('error'=>$error));
    }

}