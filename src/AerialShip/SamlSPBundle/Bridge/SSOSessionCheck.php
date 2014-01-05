<?php

namespace AerialShip\SamlSPBundle\Bridge;

use AerialShip\SamlSPBundle\Error\SSOSessionException;
use AerialShip\SamlSPBundle\RelyingParty\RelyingPartyInterface;
use AerialShip\SamlSPBundle\Security\Core\Authentication\Token\SamlSpToken;
use AerialShip\SamlSPBundle\State\SSO\SSOStateStoreInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\HttpUtils;


class SSOSessionCheck implements RelyingPartyInterface
{
    /** @var  string */
    protected $providerKey;

    /** @var \Symfony\Component\Security\Core\SecurityContextInterface  */
    protected $securityContext;

    /** @var \AerialShip\SamlSPBundle\State\SSO\SSOStateStoreInterface  */
    protected $ssoStore;

    /** @var \Symfony\Component\Security\Http\HttpUtils  */
    protected $httpUtils;


    function __construct($providerKey, SecurityContextInterface $securityContext, SSOStateStoreInterface $ssoStore, HttpUtils $httpUtils)
    {
        $this->providerKey = $providerKey;
        $this->securityContext = $securityContext;
        $this->ssoStore = $ssoStore;
        $this->httpUtils = $httpUtils;
    }



    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return bool
     */
    public function supports(Request $request)
    {
        if ($this->httpUtils->checkRequestPath($request, $request->attributes->get('failure_path'))) {
            return false;
        }
        $token = $this->securityContext->getToken();
        $result = $token != null
                && $token->isAuthenticated()
                && $token instanceof SamlSpToken
                && $token->getSamlSpInfo() != null
                && $token->getSamlSpInfo()->getAuthnStatement() != null
        ;
        return $result;
    }


    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @throws \AerialShip\SamlSPBundle\Error\SSOSessionException
     * @return \Symfony\Component\HttpFoundation\Response|SamlSpInfo|null
     */
    public function manage(Request $request)
    {
        /** @var SamlSpToken $token */
        $token = $this->securityContext->getToken();
        $samlSpInfo = $token->getSamlSpInfo();

        $ssoState = $this->ssoStore->getOneByNameIDSessionIndex(
            $token->getProviderKey(),
            $samlSpInfo->getAuthenticationServiceID(),
            $samlSpInfo->getNameID()->getValue(),
            $samlSpInfo->getAuthnStatement()->getSessionIndex()
        );
        if ($ssoState == null || $ssoState->getNameID() != $samlSpInfo->getNameID()->getValue()) {
            $this->securityContext->setToken(new AnonymousToken($this->providerKey, 'anon.'));
            $ex = new SSOSessionException('SSO session has expired');
            $ex->setToken($token);
            throw $ex;
        }

        return null;
    }

} 