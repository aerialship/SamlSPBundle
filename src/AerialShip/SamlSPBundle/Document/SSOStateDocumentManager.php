<?php

namespace AerialShip\SamlSPBundle\Document;

use AerialShip\SamlSPBundle\State\SSO\SSOState;
use AerialShip\SamlSPBundle\State\SSO\SSOStateStoreInterface;
use Doctrine\ODM\MongoDB\DocumentManager;


class SSOStateDocumentManager implements SSOStateStoreInterface
{
    /** @var DocumentManager  */
    protected $documentManager;

    /** @var  string */
    protected $documentClass;


    function __construct(DocumentManager $entityManager, $entityClass) {
        $this->documentManager = $entityManager;
        $this->documentClass = $entityClass;
    }


    /**
     * @throws \RuntimeException
     * @return SSOState
     */
    function create() {
        $class = $this->documentClass;
        $result = new $class();
        if (!$result instanceof SSOStateEntity) {
            throw new \RuntimeException("Specified document class $this->documentClass is not child of SSOState");
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
        $this->documentManager->persist($state);
        $this->documentManager->flush();
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
        $this->documentManager->remove($state);
        $this->documentManager->flush();
    }


    /**
     * @return \Doctrine\ODM\MongoDB\DocumentManager
     */
    protected function getRepository()
    {
        return $this->documentManager->getRepository($this->documentClass);
    }
}