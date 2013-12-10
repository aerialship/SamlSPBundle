<?php

namespace AerialShip\SamlSPBundle\Bridge;

use AerialShip\LightSaml\Binding\BindingDetector;
use Symfony\Component\HttpFoundation\Request;


class BindingManager extends BindingDetector
{

    /**
     * @param Request $request
     * @return null|string
     */
    public function getBindingType(Request $request)
    {
        $bindingRequest = $this->getBindingRequest($request);
        $bindingType = $this->getBinding($bindingRequest);
        return $bindingType;
    }

    /**
     * @param Request $request
     * @return \AerialShip\LightSaml\Model\Protocol\Message|null
     */
    public function receive(Request $request)
    {
        $result = null;
        $bindingRequest = $this->getBindingRequest($request);
        $bindingType = $this->getBinding($bindingRequest);
        if ($bindingType) {
            $binding = $this->instantiate($bindingType);
            $result = $binding->receive($bindingRequest);
        }
        return $result;
    }


    /**
     * @param Request $request
     * @return \AerialShip\LightSaml\Binding\Request
     */
    public function getBindingRequest(Request $request)
    {
        $result = new \AerialShip\LightSaml\Binding\Request();
        $result->setQueryString($request->getQueryString());
        $result->setGet($request->query->all());
        $result->setPost($request->request->all());
        $result->setRequestMethod($request->getMethod());
        return $result;
    }


} 