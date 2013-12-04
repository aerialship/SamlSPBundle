<?php

namespace AerialShip\SamlSPBundle\Security\Http\Firewall;

use AerialShip\SamlSPBundle\Bridge\SamlSpInfo;
use AerialShip\SamlSPBundle\Error\RelyingPartyNotSetException;
use AerialShip\SamlSPBundle\RelyingParty\RelyingPartyInterface;
use AerialShip\SamlSPBundle\Security\Core\Token\SamlSpToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener;


class SamlSpAuthenticationListener extends AbstractAuthenticationListener
{
    /** @var RelyingPartyInterface */
    protected $relyingParty;


    public function setRelyingParty(RelyingPartyInterface $relyingParty) {
        $this->relyingParty = $relyingParty;
    }


    /**
     * @return RelyingPartyInterface
     * @throws RelyingPartyNotSetException
     */
    protected function getRelyingParty() {
        if (false == $this->relyingParty) {
            throw new RelyingPartyNotSetException('The relying party is required for the listener work, but it was not set. Seems like miss configuration');
        }
        return $this->relyingParty;
    }

    /**
     * {@inheritdoc}
     */
    protected function requiresAuthentication(Request $request)
    {
        return true;
    }


    /**
     * Performs authentication.
     * @param Request $request A Request instance
     * @throws \Exception
     * @throws \Symfony\Component\Security\Core\Exception\AuthenticationException
     * @throws \RuntimeException
     * @return TokenInterface|Response|null The authenticated token, null if full authentication is not possible, or a Response
     */
    protected function attemptAuthentication(Request $request)
    {
        $myRequest = $request->duplicate();
        if (!empty($this->options['login_path'])) {
            $myRequest->attributes->set('login_path', $this->options['login_path']);
        }
        if (!empty($this->options['check_path'])) {
            $myRequest->attributes->set('check_path', $this->options['check_path']);
        }
        if (!empty($this->options['logout_path'])) {
            $myRequest->attributes->set('logout_path', $this->options['logout_path']);
        }
        if (!empty($this->options['metadata_path'])) {
            $myRequest->attributes->set('metadata_path', $this->options['metadata_path']);
        }
        if (!empty($this->options['discovery_path'])) {
            $myRequest->attributes->set('discovery_path', $this->options['discovery_path']);
        }
        if (!empty($this->options['failure_path'])) {
            $myRequest->attributes->set('failure_path', $this->options['failure_path']);
        }


        if (!$this->getRelyingParty()->supports($myRequest)) {
            return null;
        }

        $result = $this->getRelyingParty()->manage($myRequest);

        if ($result instanceof Response) {
            return $result;
        }

        if ($result instanceof SamlSpInfo) {
            $token = new SamlSpToken($this->providerKey);
            $token->setSamlSpInfo($result);
            try {
                return $this->authenticationManager->authenticate($token);
            } catch (AuthenticationException $e) {
                $e->setToken($token);
                throw $e;
            }
        }
        return null;
    }

} 