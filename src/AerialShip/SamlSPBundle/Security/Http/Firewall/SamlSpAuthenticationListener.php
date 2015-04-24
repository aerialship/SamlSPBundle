<?php

namespace AerialShip\SamlSPBundle\Security\Http\Firewall;

use AerialShip\SamlSPBundle\Bridge\SamlSpInfo;
use AerialShip\SamlSPBundle\Error\RelyingPartyNotSetException;
use AerialShip\SamlSPBundle\RelyingParty\RelyingPartyInterface;
use AerialShip\SamlSPBundle\Security\Core\Authentication\Token\SamlSpToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener;

class SamlSpAuthenticationListener extends AbstractAuthenticationListener
{
    /** @var RelyingPartyInterface */
    protected $relyingParty;


    public function setRelyingParty(RelyingPartyInterface $relyingParty)
    {
        $this->relyingParty = $relyingParty;
    }


    /**
     * @return RelyingPartyInterface
     * @throws RelyingPartyNotSetException
     */
    protected function getRelyingParty()
    {
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
        $this->copyOptionsToRequestAttributes($myRequest);

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


    protected function copyOptionsToRequestAttributes(Request $myRequest)
    {
        $options = array('login_path', 'check_path', 'logout_path', 'metadata_path', 'discovery_path',
            'failure_path', 'local_logout_path'
        );
        foreach ($options as $name) {
            if (!empty($this->options[$name])) {
                $myRequest->attributes->set($name, $this->options[$name]);
            }
        }
    }
}
