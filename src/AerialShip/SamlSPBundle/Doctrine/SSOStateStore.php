<?php

namespace AerialShip\SamlSPBundle\Doctrine;

use AerialShip\SamlSPBundle\Model\SSOState;
use AerialShip\SamlSPBundle\State\SSO\AbstractSSOStateStore;
use AerialShip\SamlSPBundle\State\SSO\SSOStateStoreInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * @api
 */
class SSOStateStore extends AbstractSSOStateStore
{
    /** @var ObjectManager  */
    protected $objectManager;

    /**
     * @param ObjectManager $objectManager
     * @param string        $entityClass
     */
    public function __construct(ObjectManager $objectManager, $entityClass)
    {
        parent::__construct($entityClass);

        $this->objectManager = $objectManager;
    }

    /**
     * @param SSOState $state
     *
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
     *
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
    public function getOneByNameIDSessionIndex($providerID, $authenticationServiceName, $nameID, $sessionIndex)
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
     *
     * @return void
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
