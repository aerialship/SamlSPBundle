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
        $this->data[$provider->getAuthenticationService()] = $provider;
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


    /**
     * @param string $as
     * @return MetaProvider|null
     */
    public function findByAS($as) {
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