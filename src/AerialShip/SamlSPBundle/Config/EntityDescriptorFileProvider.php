<?php

namespace AerialShip\SamlSPBundle\Config;

use AerialShip\LightSaml\Model\Metadata\EntityDescriptor;
use Symfony\Component\HttpKernel\KernelInterface;


class EntityDescriptorFileProvider implements EntityDescriptorProviderInterface
{
    /** @var  KernelInterface */
    protected $kernel;

    /** @var  string */
    protected $filename;

    /** @var  EntityDescriptor|null */
    private $entityDescriptor;



    function __construct(KernelInterface $kernel) {
        $this->kernel = $kernel;
    }


    /**
     * @param string $filename
     * @throws \InvalidArgumentException
     */
    public function setFilename($filename) {
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
    public function getFilename() {
        return $this->filename;
    }




    /**
     * @return EntityDescriptor
     */
    public function getEntityDescriptor() {
        if ($this->entityDescriptor === null) {
            $this->load();
        }
        return $this->entityDescriptor;
    }


    protected function load() {
        $doc = new \DOMDocument();
        $doc->load($this->filename);
        $this->entityDescriptor = new EntityDescriptor();
        $this->entityDescriptor->loadFromXml($doc->firstChild);
    }

} 