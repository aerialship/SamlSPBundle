<?php

namespace AerialShip\SamlSPBundle\Config;

use AerialShip\LightSaml\Model\Metadata\EntitiesDescriptor;
use AerialShip\LightSaml\Model\Metadata\EntityDescriptor;
use Symfony\Component\HttpKernel\KernelInterface;

class EntityDescriptorFileProvider implements EntityDescriptorProviderInterface
{
    /** @var  KernelInterface */
    protected $kernel;

    /** @var  string */
    protected $filename;

    /** @var  string|null */
    protected $entityId;

    /** @var  EntityDescriptor|null */
    private $entityDescriptor;



    function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }


    /**
     * @param string $filename
     * @throws \InvalidArgumentException
     */
    public function setFilename($filename)
    {
        if ($filename && $filename[0] == '@') {
            $filename = $this->kernel->locateResource($filename);
        }
        if (!is_file($filename)) {
            throw new \InvalidArgumentException('Specified file does not exist: '.$filename);
        }
        $this->filename = $filename;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param null|string $entityId
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;
    }

    /**
     * @return null|string
     */
    public function getEntityId()
    {
        return $this->entityId;
    }




    /**
     * @return EntityDescriptor
     */
    public function getEntityDescriptor()
    {
        if ($this->entityDescriptor === null) {
            $this->load();
        }
        return $this->entityDescriptor;
    }


    protected function load()
    {
        $doc = new \DOMDocument();
        $doc->load($this->filename);
        if ($this->entityId) {
            $entitiesDescriptor = new EntitiesDescriptor();
            $entitiesDescriptor->loadFromXml($doc->firstChild);
            $this->entityDescriptor = $entitiesDescriptor->getByEntityId($this->entityId);
        } else {
            $this->entityDescriptor = new EntityDescriptor();
            $this->entityDescriptor->loadFromXml($doc->firstChild);
        }
    }
}
