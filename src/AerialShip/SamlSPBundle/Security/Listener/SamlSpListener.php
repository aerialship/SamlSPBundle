<?php

namespace AerialShip\SamlSPBundle\Security\Listener;

use AerialShip\SamlSPBundle\Bridge\SamlSpResponse;
use AerialShip\SamlSPBundle\RelyingParty\RelyingPartyInterface;
use AerialShip\SamlSPBundle\Security\Token\SamlSpToken;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener;

class SamlSpListener extends AbstractAuthenticationListener
{
    /** @var RelyingPartyInterface */
    protected $relyingParty;


    public function setRelyingParty(RelyingPartyInterface $relyingParty) {
        $this->relyingParty = $relyingParty;
    }


    /**
     * @return RelyingPartyInterface
     * @throws \RuntimeException
     */
    protected function getRelyingParty() {
        if (false == $this->relyingParty) {
            throw new \RuntimeException('The relying party is required for the listener work, but it was not set. Seems like miss configuration');
        }
        return $this->relyingParty;
    }

    /**
     * {@inheritdoc}
     */
    protected function requiresAuthentication(Request $request) {
        $pathInfo = $request->getPathInfo();
        $result = false;
        if ($pathInfo == $this->options['login_path']) {
            $result = true;
        } else if ($pathInfo == $this->options['check_path']) {
            $result = true;
        } else if ($pathInfo == $this->options['logout_path']) {
            $result = true;
        }
        if (!$result) {
            $result = parent::requiresAuthentication($request);
        }
        return $result; //$this->getRelyingParty()->supports($request);
    }


    /**
     * Performs authentication.
     * @param Request $request A Request instance
     * @throws \Exception
     * @throws \Symfony\Component\Security\Core\Exception\AuthenticationException
     * @throws \RuntimeException
     * @return TokenInterface|Response|null The authenticated token, null if full authentication is not possible, or a Response
     */
    protected function attemptAuthentication(Request $request) {
        $myRequest = $request->duplicate();
        if (false == empty($this->options['login_path'])) {
            $myRequest->attributes->set('login_path', $this->options['login_path']);
        }
        if (false == empty($this->options['check_path'])) {
            $myRequest->attributes->set('check_path', $this->options['check_path']);
        }
        if (false == empty($this->options['logout_path'])) {
            $myRequest->attributes->set('logout_path', $this->options['logout_path']);
        }

        $result = $this->getRelyingParty()->manage($myRequest);

        if ($result instanceof Response) {
            return $result;
        }

        if ($result instanceof SamlSpResponse) {
            $token = new SamlSpToken($this->providerKey);
            $token->setAttributes($result->getAttributes());
            try {
                return $this->authenticationManager->authenticate($token);
            } catch (AuthenticationException $e) {
                $e->setToken($token);
                throw $e;
            }
        }

        throw new \RuntimeException(sprintf(
            'The relying party %s::manage() must either return a Response or instance of SamlSpResponse.',
            get_class($this->getRelyingParty())
        ));
    }

} 