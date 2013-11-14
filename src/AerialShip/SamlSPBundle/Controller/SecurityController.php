<?php

namespace AerialShip\SamlSPBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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


}