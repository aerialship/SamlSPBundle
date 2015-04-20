<?php

namespace AerialShip\SamlSPBundle\Tests\Config;

use AerialShip\SamlSPBundle\Config\EntityDescriptorProviderInterface;
use AerialShip\SamlSPBundle\Config\ServiceInfo;
use AerialShip\SamlSPBundle\Config\SpMetaProviderInterface;
use AerialShip\SamlSPBundle\Config\SPSigningProviderInterface;

class ServiceInfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeConstructedWithRequiredArgs()
    {
        new ServiceInfo(
            $expectedProviderID = 'main',
            $expectedIDPID = 'idp',
            $expectedSPProvider = $this->createEntityDescriptorProviderMock(),
            $expectedIDPProvider = $this->createEntityDescriptorProviderMock(),
            $expectedSPMeta = $this->createSpMetaProviderMock(),
            $expectedSigning = $this->createSPSigningProviderMock()
        );
    }

    /**
     * @test
     */
    public function shouldAllowGetProviderIDWithValueFromConstructor()
    {
        $si = new ServiceInfo(
            $expectedProviderID = 'main',
            $expectedIDPID = 'idp',
            $expectedSPProvider = $this->createEntityDescriptorProviderMock(),
            $expectedIDPProvider = $this->createEntityDescriptorProviderMock(),
            $expectedSPMeta = $this->createSpMetaProviderMock(),
            $expectedSigning = $this->createSPSigningProviderMock()
        );

        $this->assertEquals($expectedProviderID, $si->getProviderID());
    }


    /**
     * @test
     */
    public function shouldAllowGetAuthenticationServiceWithValueFromConstructor()
    {
        $si = new ServiceInfo(
            $expectedProviderID = 'main',
            $expectedIDPID = 'idp',
            $expectedSPProvider = $this->createEntityDescriptorProviderMock(),
            $expectedIDPProvider = $this->createEntityDescriptorProviderMock(),
            $expectedSPMeta = $this->createSpMetaProviderMock(),
            $expectedSigning = $this->createSPSigningProviderMock()
        );

        $this->assertEquals($expectedIDPID, $si->getAuthenticationService());
    }

    /**
     * @test
     */
    public function shouldAllowGetSPProviderWithValueFromConstructor()
    {
        $si = new ServiceInfo(
            $expectedProviderID = 'main',
            $expectedIDPID = 'idp',
            $expectedSPProvider = $this->createEntityDescriptorProviderMock(),
            $expectedIDPProvider = $this->createEntityDescriptorProviderMock(),
            $expectedSPMeta = $this->createSpMetaProviderMock(),
            $expectedSigning = $this->createSPSigningProviderMock()
        );

        $this->assertEquals($expectedSPProvider, $si->getSpProvider());
    }

    /**
     * @test
     */
    public function shouldAllowGetIDPProviderWithValueFromConstructor()
    {
        $si = new ServiceInfo(
            $expectedProviderID = 'main',
            $expectedIDPID = 'idp',
            $expectedSPProvider = $this->createEntityDescriptorProviderMock(),
            $expectedIDPProvider = $this->createEntityDescriptorProviderMock(),
            $expectedSPMeta = $this->createSpMetaProviderMock(),
            $expectedSigning = $this->createSPSigningProviderMock()
        );

        $this->assertEquals($expectedIDPProvider, $si->getIdpProvider());
    }

    /**
     * @test
     */
    public function shouldAllowGetSpMetaProviderWithValueFromConstructor()
    {
        $si = new ServiceInfo(
            $expectedProviderID = 'main',
            $expectedIDPID = 'idp',
            $expectedSPProvider = $this->createEntityDescriptorProviderMock(),
            $expectedIDPProvider = $this->createEntityDescriptorProviderMock(),
            $expectedSPMeta = $this->createSpMetaProviderMock(),
            $expectedSigning = $this->createSPSigningProviderMock()
        );

        $this->assertEquals($expectedSPMeta, $si->getSpMetaProvider());
    }

    /**
     * @test
     */
    public function shouldAllowGetSpSigningProviderWithValueFromConstructor()
    {
        $si = new ServiceInfo(
            $expectedProviderID = 'main',
            $expectedIDPID = 'idp',
            $expectedSPProvider = $this->createEntityDescriptorProviderMock(),
            $expectedIDPProvider = $this->createEntityDescriptorProviderMock(),
            $expectedSPMeta = $this->createSpMetaProviderMock(),
            $expectedSigning = $this->createSPSigningProviderMock()
        );

        $this->assertEquals($expectedSigning, $si->getSpSigningProvider());
    }



    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|EntityDescriptorProviderInterface
     */
    protected function createEntityDescriptorProviderMock()
    {
        return $this->getMock('AerialShip\SamlSPBundle\Config\EntityDescriptorProviderInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SpMetaProviderInterface
     */
    protected function createSpMetaProviderMock()
    {
        return $this->getMock('AerialShip\SamlSPBundle\Config\SpMetaProviderInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SPSigningProviderInterface
     */
    protected function createSPSigningProviderMock()
    {
        return $this->getMock('AerialShip\SamlSPBundle\Config\SPSigningProviderInterface');
    }
}
