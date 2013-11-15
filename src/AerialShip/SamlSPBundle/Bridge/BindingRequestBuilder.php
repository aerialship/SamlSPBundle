<?php

namespace AerialShip\SamlSPBundle\Bridge;

use Symfony\Component\HttpFoundation\Request;

class BindingRequestBuilder
{
    function getBindingRequest(Request $request) {
        $result = new \AerialShip\LightSaml\Binding\Request();
        $result->setQueryString($request->getQueryString());
        $result->setGet($request->query->all());
        $result->setPost($request->request->all());
        $result->setRequestMethod($request->getMethod());
        return $result;
    }
} 