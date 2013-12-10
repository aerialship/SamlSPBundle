<?php

namespace AerialShip\SamlSPBundle\Config;

use AerialShip\LightSaml\Security\KeyHelper;
use AerialShip\LightSaml\Security\X509Certificate;
use Symfony\Component\HttpKernel\KernelInterface;


class SPSigningProviderFile implements SPSigningProviderInterface
{
    /** @var  KernelInterface */
    protected $kernel;

    /** @var string  */
    protected $certificateFile;

    /** @var string  */
    protected $keyFile;

    /** @var  string */
    protected $keyPass;

    /** @var  X509Certificate|null */
    private $_certificate;

    /** @var  \XMLSecurityKey|null */
    private $_key;


    /**
     * @param \Symfony\Component\HttpKernel\KernelInterface $kernel
     * @param string $certificateFile
     * @param string $keyFile
     * @param string $keyPass
     */
    public function __construct(KernelInterface $kernel, $certificateFile, $keyFile, $keyPass)
    {
        $this->kernel = $kernel;
        $this->certificateFile = $certificateFile;
        $this->keyFile = $keyFile;
        $this->keyPass = $keyPass;
    }

    /**
     * @return bool
     */
    public function isEnabled() {
        return true;
    }


    /**
     * @return X509Certificate
     */
    public function getCertificate()
    {
        if (!$this->_certificate) {
            $this->_certificate = new X509Certificate();
            $filename = $this->certificateFile;
            if ($filename[0] == '@') {
                $filename = $this->kernel->locateResource($filename);
            }
            $this->_certificate->loadFromFile($filename);
        }
        return $this->_certificate;
    }

    /**
     * @return \XMLSecurityKey
     */
    public function getPrivateKey()
    {
        if (!$this->_key) {
            $filename = $this->keyFile;
            if ($filename[0] == '@') {
                $filename = $this->kernel->locateResource($filename);
            }
            $this->_key = KeyHelper::createPrivateKey($filename, $this->keyPass, true, false);
        }
        return $this->_key;
    }

}
