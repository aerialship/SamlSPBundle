<?php

namespace AerialShip\SamlSPBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;

class SecurityController extends Controller
{
    public function loginAction()
    {
        throw new \RuntimeException('You must configure the login path to be handled by the firewall using aerial_ship_saml_sp in your security firewall configuration.');
    }

    public function acsAction()
    {
        throw new \RuntimeException('You must configure the assertion consumer path path to be handled by the firewall using aerial_ship_saml_sp in your security firewall configuration.');
    }

    public function logoutAction()
    {
        throw new \RuntimeException('You must activate the logout in your security firewall configuration.');
    }

    public function logoutReceiveAction()
    {
        throw new \RuntimeException('You must configure the logout receive path path to be handled by the firewall using aerial_ship_saml_sp in your security firewall configuration.');
    }

    public function federationMetadataAction()
    {
        throw new \RuntimeException('You must configure the federation metadata path path to be handled by the firewall using aerial_ship_saml_sp in your security firewall configuration.');
    }

    public function discoveryAction()
    {
        throw new \RuntimeException('You must configure the discovery path path to be handled by the firewall using aerial_ship_saml_sp in your security firewall configuration.');
    }


    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function failureAction(Request $request)
    {
        /** @var $error AuthenticationException */
        $error = $request->getSession()->get(Security::AUTHENTICATION_ERROR);
        return $this->render('AerialShipSamlSPBundle::failure.html.twig', array('error'=>$error));
    }
}
