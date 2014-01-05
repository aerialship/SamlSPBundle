<?php

namespace AerialShip\SamlSPBundle\Entity;

use AerialShip\SamlSPBundle\State\SSO\SSOState;
use AerialShip\SamlSPBundle\State\SSO\SSOStateStoreInterface;
use Doctrine\ORM\EntityManager;


class SSOStateStoreManager implements SSOStateStoreInterface
{
    /** @var EntityManager  */
    protected $entityManager;

    /** @var  string */
    protected $entityClass;


    function __construct(EntityManager $entityManager, $entityClass) {
        $this->entityManager = $entityManager;
        $this->entityClass = $entityClass;
    }


    /**
     * @throws \RuntimeException
     * @return SSOState
     */
    function create() {
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
        $this->entityManager->persist($state);
        $this->entityManager->flush();
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
        $this->entityManager->remove($state);
        $this->entityManager->flush();
    }


    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepository()
    {
        return $this->entityManager->getRepository($this->entityClass);
    }
}