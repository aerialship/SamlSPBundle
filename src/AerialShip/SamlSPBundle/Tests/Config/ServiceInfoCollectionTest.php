<?php

namespace AerialShip\SamlSPBundle\Tests\Config;

use AerialShip\LightSaml\Model\Metadata\EntityDescriptor;
use AerialShip\SamlSPBundle\Config\EntityDescriptorProviderInterface;
use AerialShip\SamlSPBundle\Config\ServiceInfo;
use AerialShip\SamlSPBundle\Config\ServiceInfoCollection;


class ServiceInfoCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function couldBeConstructed()
    {
        new ServiceInfoCollection();
    }

    /**
     * @test
     */
    public function shouldReturnNullForNonExistingService()
    {
        $col = new ServiceInfoCollection();
        $this->assertNull($col->get('something'));
    }

    /**
     * @test
     */
    public function shouldReturnServiceInfoByIdpIDWhenGetIsCalled()
    {
        $col = new ServiceInfoCollection();

        $expectedServiceInfo_1 = $this->createServiceInfoStub(
            $expectedProviderID_1 = 'main',
            $expectedIdpID_1 = 'idp1',
            null, null, null, null
        );
        $expectedServiceInfo_2 = $this->createServiceInfoStub(
            $expectedProviderID_2 = 'main',
            $expectedIdpID_2 = 'idp2',
            null, null, null, null
        );

        $col->add($expectedServiceInfo_1);
        $col->add($expectedServiceInfo_2);

        $this->assertEquals($expectedServiceInfo_1, $col->get($expectedIdpID_1));
        $this->assertEquals($expectedServiceInfo_2, $col->get($expectedIdpID_2));
    }

    /**
     * @test
     */
    public function shouldReturnAllThatAreAddedWhenAllIsCalled()
    {
        $col = new ServiceInfoCollection();

        $expectedServiceInfo_1 = $this->createServiceInfoStub(
            $expectedProviderID_1 = 'main',
            $expectedIdpID_1 = 'idp1',
            null, null, null, null
        );
        $expectedServiceInfo_2 = $this->createServiceInfoStub(
            $expectedProviderID_2 = 'main',
            $expectedIdpID_2 = 'idp2',
            null, null, null, null
        );

        $col->add($expectedServiceInfo_1);
        $col->add($expectedServiceInfo_2);

        $all = $col->all();

        $this->assertInternalType('array', $all);
        $this->assertCount(2, $all);
        $this->assertEquals($expectedServiceInfo_1, $all[$expectedIdpID_1]);
        $this->assertEquals($expectedServiceInfo_2, $all[$expectedIdpID_2]);
    }


    /**
     * @test
     */
    public function shouldReturnEmptyArrayWhenAllCalledAndNoneAdded()
    {
        $col = new ServiceInfoCollection();

        $all = $col->all();

        $this->assertInternalType('array', $all);
        $this->assertCount(0, $all);
    }

    /**
     * @test
     */
    public function shouldReturnServiceInfoWhenFindByAsIsCalledWithNullAndThereIsOnlyOne()
    {
        $col = new ServiceInfoCollection();

        $expectedServiceInfo = $this->createServiceInfoStub(
            $expectedProviderID = 'main',
            $expectedIdpID = 'idp',
            null, null, null, null
        );

        $col->add($expectedServiceInfo);

        $this->assertEquals($expectedServiceInfo, $col->findByAS(null));
    }


    /**
     * @test
     */
    public function shouldReturnNullWhenFindByAsIsCalledWithNullAndThereIsMoreThenOne()
    {
        $col = new ServiceInfoCollection();

        $expectedServiceInfo_1 = $this->createServiceInfoStub(
            $expectedProviderID_1 = 'main',
            $expectedIdpID_1 = 'idp1',
            null, null, null, null
        );
        $expectedServiceInfo_2 = $this->createServiceInfoStub(
            $expectedProviderID_2 = 'main',
            $expectedIdpID_2 = 'idp2',
            null, null, null, null
        );

        $col->add($expectedServiceInfo_1);
        $col->add($expectedServiceInfo_2);

        $this->assertNull($col->findByAS(null));
    }

    /**
     * @test
     */
    public function shouldReturnServiceInfoWhenFindByAsIsCalledWithIdpIDAndThereIsMoreThenOne()
    {
        $col = new ServiceInfoCollection();

        $expectedServiceInfo_1 = $this->createServiceInfoStub(
            $expectedProviderID_1 = 'main',
            $expectedIdpID_1 = 'idp1',
            null, null, null, null
        );
        $expectedServiceInfo_2 = $this->createServiceInfoStub(
            $expectedProviderID_2 = 'main',
            $expectedIdpID_2 = 'idp2',
            null, null, null, null
        );

        $col->add($expectedServiceInfo_1);
        $col->add($expectedServiceInfo_2);

        $this->assertEquals($expectedServiceInfo_1, $col->findByAS($expectedIdpID_1));
        $this->assertEquals($expectedServiceInfo_2, $col->findByAS($expectedIdpID_2));
    }


    /**
     * @test
     */
    public function shouldReturnServiceInfoWhenFindByIDPEntityIDIsCalledWithEntityID()
    {
        $expectedProviderID = 'main';

        $col = new ServiceInfoCollection();

        $ed_1 = $this->createEntityDescriptorStub($expectedEntityID_1 = 'entity_1');
        $expectedIdpProvider_1 = $this->createEntityDescriptorProviderStub($ed_1);
        $expectedServiceInfo_1 = $this->createServiceInfoStub(
            $expectedProviderID,
            $expectedIdpID_1 = 'idp_1',
            $expectedSpProvider = null,
            $expectedIdpProvider_1,
            null, null
        );

        $ed_2 = $this->createEntityDescriptorStub($expectedEntityID_2 = 'entity_2');
        $expectedIdpProvider_2 = $this->createEntityDescriptorProviderStub($ed_2);
        $expectedServiceInfo_2 = $this->createServiceInfoStub(
            $expectedProviderID,
            $expectedIdpID_2 = 'idp_2',
            $expectedSpProvider = null,
            $expectedIdpProvider_2,
            null, null
        );

        $col->add($expectedServiceInfo_1);
        $col->add($expectedServiceInfo_2);

        $this->assertEquals($expectedServiceInfo_1, $col->findByIDPEntityID($expectedEntityID_1));
        $this->assertEquals($expectedServiceInfo_2, $col->findByIDPEntityID($expectedEntityID_2));
        $this->assertNull($col->findByIDPEntityID('foo'));
    }


    /**
     * @test
     */
    public function shouldReturnNullWhenFindByIDPEntityIDIsCalledWithUnknownEntityID()
    {
        $col = new ServiceInfoCollection();

        $this->assertNull($col->findByIDPEntityID('foo'));
    }





    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ServiceInfo
     */
    protected function createServiceInfoMock()
    {
        return $this->getMock('AerialShip\SamlSPBundle\Config\ServiceInfo', array(), array(), '', false, false);
    }


    /**
     * @param $providerID
     * @param $idpID
     * @param $spProvider
     * @param $idpProvider
     * @param $spMetaProvider
     * @param $signingProvider
     * @return ServiceInfo|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createServiceInfoStub($providerID, $idpID, $spProvider, $idpProvider, $spMetaProvider, $signingProvider)
    {
        $result = $this->createServiceInfoMock();
        $result->expects($this->any())->method('getProviderID')->will($this->returnValue($providerID));
        $result->expects($this->any())->method('getAuthenticationService')->will($this->returnValue($idpID));
        $result->expects($this->any())->method('getSpProvider')->will($this->returnValue($spProvider));
        $result->expects($this->any())->method('getIdpProvider')->will($this->returnValue($idpProvider));
        $result->expects($this->any())->method('getSpMetaProvider')->will($this->returnValue($spMetaProvider));
        $result->expects($this->any())->method('getSpSigningProvider')->will($this->returnValue($signingProvider));
        return $result;
    }


    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|EntityDescriptorProviderInterface
     */
    protected function createEntityDescriptorProviderMock()
    {
        return $this->getMock('AerialShip\SamlSPBundle\Config\EntityDescriptorProviderInterface');
    }

    /**
     * @param $ed
     * @return EntityDescriptorProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createEntityDescriptorProviderStub($ed)
    {
        $result = $this->createEntityDescriptorProviderMock();
        $result->expects($this->any())->method('getEntityDescriptor')->will($this->returnValue($ed));
        return $result;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|EntityDescriptor
     */
    protected function createEntityDescriptorMock()
    {
        return $this->getMock('AerialShip\LightSaml\Model\Metadata\EntityDescriptor');
    }

    /**
     * @param $entityID
     * @return EntityDescriptor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createEntityDescriptorStub($entityID)
    {
        $result = $this->createEntityDescriptorMock();
        $result->expects($this->any())->method('getEntityID')->will($this->returnValue($entityID));
        return $result;
    }


}
