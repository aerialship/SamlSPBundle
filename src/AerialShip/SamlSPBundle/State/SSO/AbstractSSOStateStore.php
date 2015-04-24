<?php

namespace AerialShip\SamlSPBundle\State\SSO;

use AerialShip\SamlSPBundle\Model\SSOState;

abstract class AbstractSSOStateStore implements SSOStateStoreInterface
{
    /** @var  string */
    protected $entityClass;

    /**
     * @param $entityClass
     */
    public function __construct($entityClass)
    {
        $this->entityClass = $entityClass;
    }

    /**
     * @throws \RuntimeException
     *
     * @return SSOState
     */
    public function create()
    {
        $class = $this->entityClass;
        $result = new $class();
        if ($result instanceof SSOState) {
            $result->setCreatedOn(new \DateTime());

            return $result;
        }
        throw new \RuntimeException(sprintf('Specified entity class "%s" is not child of SSOState', $this->entityClass));
    }
}
