<?php

namespace AerialShip\SamlSPBundle\Config;


class MetaProviderCollection
{
    /** @var MetaProvider[] */
    private $data = array();


    /**
     * @param MetaProvider $provider
     */
    public function add(MetaProvider $provider)
    {
        $this->data[$provider->getId()] = $provider;
    }


    /**
     * @param string $id
     * @return MetaProvider|null
     */
    public function get($id)
    {
        if (isset($this->data[$id])) {
            return $this->data[$id];
        }
        return null;
    }


    /**
     * @return MetaProvider[]
     */
    public function all()
    {
        return $this->data;
    }


    /**
     * @param string $entityID
     * @return MetaProvider|null
     */
    public function findByEntityID($entityID)
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
} 