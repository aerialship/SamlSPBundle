<?php

namespace AerialShip\SamlSPBundle\Bridge;

use AerialShip\LightSaml\Binding\BindingDetector;
use AerialShip\LightSaml\Model\Protocol\Response;
use AerialShip\SamlSPBundle\RelyingParty\RelyingPartyInterface;
use Symfony\Component\HttpFoundation\Request;


class AssertionConsumer implements RelyingPartyInterface
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return bool
     */
    function supports(Request $request) {
        $result = $request->attributes->get('check_path') == $request->getPathInfo();
        return $result;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @throws \InvalidArgumentException if cannot manage the Request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|SamlSpResponse
     */
    function manage(Request $request) {
        $bReq = new \AerialShip\LightSaml\Binding\Request();
        $bReq->setQueryString($request->getQueryString());
        $bReq->setGet($request->query->all());
        $bReq->setPost($request->request->all());
        $bReq->setRequestMethod($request->getMethod());

        $detector = new BindingDetector();
        $bType = $detector->getBinding($bReq);
        $binding = $detector->instantiate($bType);
        /** @var Response $response */
        $response = $binding->receive($bReq);

        var_dump($response);
        exit;
    }

} 