<?php

namespace AerialShip\SamlSPBundle\Config;

class ServiceInfoCollection
{
    /** @var ServiceInfo[] */
    private $data = array();


    /**
     * @param ServiceInfo $serviceInfo
     */
    public function add(ServiceInfo $serviceInfo)
    {
        $this->data[$serviceInfo->getAuthenticationService()] = $serviceInfo;
    }


    /**
     * @param string $id
     * @return ServiceInfo|null
     */
    public function get($id)
    {
        if (isset($this->data[$id])) {
            return $this->data[$id];
        }
        return null;
    }


    /**
     * @return ServiceInfo[]
     */
    public function all()
    {
        return $this->data;
    }


    /**
     * @param string $entityID
     * @return ServiceInfo|null
     */
    public function findByIDPEntityID($entityID)
    {
        $result = null;
        foreach ($this->data as $provider) {
            if ($entityID == $provider->getIdpProvider()->getEntityDescriptor()->getEntityID()) {
                $result = $provider;
                break;
            }
        }
        return $result;
    }


    /**
     * @param string|null $as
     * @return ServiceInfo|null
     */
    public function findByAS($as)
    {
        $result = null;
        if (!$as && count($this->data)==1) {
            $arr = $this->data;
            $result = array_pop($arr);
        } else if ($as) {
            $result = $this->get($as);
        }
        return $result;
    }
}
