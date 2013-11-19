<?php

namespace AerialShip\SamlSPBundle\State\SSO;

use AerialShip\SamlSPBundle\State\StateInterface;
use AerialShip\SamlSPBundle\State\StateStoreInterface;
use Symfony\Component\HttpKernel\KernelInterface;


class StateStoreFile implements StateStoreInterface
{
    /** @var \Symfony\Component\HttpKernel\KernelInterface  */
    protected $kernel;



    function __construct(KernelInterface $kernel) {
        $this->kernel = $kernel;
    }


    /**
     * @param StateInterface $state
     * @return void
     */
    function set(StateInterface $state) {
        $file = $this->getDir().$state->getStateID();
        file_put_contents($file, serialize($state));
    }

    /**
     * @param string $id
     * @return StateInterface|null
     */
    function get($id) {
        $file = $this->getDir().$id;
        if (is_file($file)) {
            $result = unserialize(file_get_contents($file));
            return $result;
        }
        return null;
    }

    /**
     * @param string $id
     * @return bool
     */
    function remove($id) {
        $file = $this->getDir().$id;
        if (is_file($file)) {
            unlink($file);
            return true;
        }
        return false;
    }



    protected function getDir() {
        return $this->kernel->getCacheDir().'/saml/';
    }

} 