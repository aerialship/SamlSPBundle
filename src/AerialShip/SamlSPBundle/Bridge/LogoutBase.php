<?php

namespace AerialShip\SamlSPBundle\Bridge;

use AerialShip\SamlSPBundle\Config\ServiceInfo;
use AerialShip\SamlSPBundle\State\SSO\SSOState;
use AerialShip\SamlSPBundle\State\SSO\SSOStateStoreInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\HttpUtils;


class LogoutBase
{
    /** @var SSOStateStoreInterface  */
    protected $ssoStore;

    /** @var \Symfony\Component\Security\Http\HttpUtils  */
    protected $httpUtils;



    public function __construct(SSOStateStoreInterface $ssoStore, HttpUtils $httpUtils)
    {
        $this->ssoStore = $ssoStore;
        $this->httpUtils = $httpUtils;
    }


    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function getLogoutRedirectResponse(Request $request)
    {
        return $this->httpUtils->createRedirectResponse($request, $request->attributes->get('local_logout_path'));
    }

    /**
     * @param ServiceInfo $serviceInfo
     * @param string $nameID
     * @param string $sessionIndex
     * @return SSOState[]
     */
    protected function getSSOState(ServiceInfo $serviceInfo, $nameID, $sessionIndex)
    {
        if ($sessionIndex) {
            $result = array();
            $state = $this->ssoStore->getOneByNameIDSessionIndex(
                $serviceInfo->getProviderID(),
                $serviceInfo->getAuthenticationService(),
                $nameID,
                $sessionIndex
            );
            if ($state) {
                $result[] = $state;
            }
        } else {
            $result = $this->ssoStore->getAllByNameID(
                $serviceInfo->getProviderID(),
                $serviceInfo->getAuthenticationService(),
                $nameID
            );
        }
        return $result;
    }


    protected function deleteSSOState(array $arrStates)
    {
        foreach ($arrStates as $state) {
            $this->ssoStore->remove($state);
        }
    }

} 