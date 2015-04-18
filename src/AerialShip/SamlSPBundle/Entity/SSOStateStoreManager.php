<?php

namespace AerialShip\SamlSPBundle\Entity;

use AerialShip\SamlSPBundle\State\SSO\SSOState;
use AerialShip\SamlSPBundle\State\SSO\SSOStateStoreInterface;
use Doctrine\Common\Persistence\ObjectManager;

class SSOStateStoreManager implements SSOStateStoreInterface
{
    /** @var ObjectManager  */
    protected $objectManager;

    /** @var  string */
    protected $entityClass;


    function __construct(ObjectManager $objectManager, $entityClass)
    {
        $this->objectManager = $objectManager;
        $this->entityClass = $entityClass;
    }


    /**
     * @throws \RuntimeException
     * @return SSOState
     */
    function create()
    {
        $class = $this->entityClass;
        $result = new $class();
        if (!$result instanceof SSOStateEntity) {
            throw new \RuntimeException("Specified entity class $this->entityClass is not child of SSOState");
        }
        $result->setCreatedOn(new \DateTime());
        return $result;
    }


    /**
     * @param SSOState $state
     * @return void
     */
    public function set(SSOState $state)
    {
        $this->objectManager->persist($state);
        $this->objectManager->flush();
    }


    /**
     * @param string $providerID
     * @param string $authenticationServiceName
     * @param string $nameID
     * @return SSOState[]
     */
    public function getAllByNameID($providerID, $authenticationServiceName, $nameID)
    {
        return $this->getRepository()->findBy(
            array(
                'providerID' => $providerID,
                'authenticationServiceName' => $authenticationServiceName,
                'nameID' => $nameID
            )
        );
    }

    /**
     * @param string $providerID
     * @param string $authenticationServiceName
     * @param string $nameID
     * @param string $sessionIndex
     * @return SSOState
     */
    function getOneByNameIDSessionIndex($providerID, $authenticationServiceName, $nameID, $sessionIndex)
    {
        return $this->getRepository()->findOneBy(
            array(
                'providerID' => $providerID,
                'authenticationServiceName' => $authenticationServiceName,
                'nameID' => $nameID,
                'sessionIndex' => $sessionIndex
            )
        );
    }


    /**
     * @param string $providerID
     * @param string $authenticationServiceName
     * @param string $nameID
     * @param string $sessionIndex
     * @return SSOState|null
     */
    public function get($providerID, $authenticationServiceName, $nameID, $sessionIndex)
    {
        return $this->getRepository()->findOneBy(
            array(
                'providerID' => $providerID,
                'authenticationServiceName' => $authenticationServiceName,
                'nameID' => $nameID,
                'sessionIndex' => $sessionIndex
            )
        );
    }

    /**
     * @param SSOState $state
     * @return bool
     */
    public function remove(SSOState $state)
    {
        $this->objectManager->remove($state);
        $this->objectManager->flush();
    }


    /**
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getRepository()
    {
        return $this->objectManager->getRepository($this->entityClass);
    }
}
