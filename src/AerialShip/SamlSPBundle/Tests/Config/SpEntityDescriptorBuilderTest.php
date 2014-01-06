<?php

namespace AerialShip\SamlSPBundle\Tests\Config;

use AerialShip\LightSaml\Bindings;
use AerialShip\SamlSPBundle\Config\SpEntityDescriptorBuilder;
use AerialShip\SamlSPBundle\Config\SPSigningProviderInterface;


class SpEntityDescriptorBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementEntityDescriptorProviderInterface()
    {
        $rc = new \ReflectionClass('AerialShip\SamlSPBundle\Config\SpEntityDescriptorBuilder');
        $this->assertTrue($rc->implementsInterface('AerialShip\SamlSPBundle\Config\EntityDescriptorProviderInterface'));
    }

    /**
     * @test
     */
    public function shouldBeConstructedWithRequiredArguments()
    {
        new SpEntityDescriptorBuilder(
            'idp',
            $this->createSPSigningProviderMock(),
            array('entity_id'=>'entityID', 'base_url'=>'http://site.com'),
            'check_path',
            'logout_path',
            $this->createHttpUtilsMock()
        );
    }

    /**
     * @test
     */
    public function shouldReturnIdpIDSetInConstructor()
    {
        $builder = new SpEntityDescriptorBuilder(
            $expectedIDP = 'idp',
            $this->createSPSigningProviderMock(),
            array('entity_id'=>'entityID', 'base_url'=>'http://site.com'),
            'check_path',
            'logout_path',
            $this->createHttpUtilsMock()
        );

        $this->assertEquals($expectedIDP, $builder->getAuthenticationServiceID());
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Missing required config entity_id
     */
    public function shouldThrowIfConstructedWithoutEntityID()
    {
        new SpEntityDescriptorBuilder(
            'idp',
            $this->createSPSigningProviderMock(),
            array('base_url'=>'http://site.com'),
            'check_path',
            'logout_path',
            $this->createHttpUtilsMock()
        );
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage If config base_url is not set, then httpUtils are required
     */
    public function shouldThrowIfConstructedWithoutBaseUrlAndHttpUtils()
    {
        new SpEntityDescriptorBuilder(
            'idp',
            $this->createSPSigningProviderMock(),
            array('entity_id'=>'entityID'),
            'check_path',
            'logout_path',
            null
        );
    }


    /**
     * @test
     */
    public function shouldBuildWithSigningDisabledAndBasePathSet()
    {
        $signingProvider = $this->createSPSigningProviderStub(false, null, null);
        $expectedEntityID = 'entityID';
        $expectedBaseUrl = 'http://site.com';
        $checkPath = '/check_path';
        $logoutPath = '/logout_path';

        $builder = new SpEntityDescriptorBuilder(
            'idp',
            $signingProvider,
            array('entity_id'=>$expectedEntityID, 'base_url'=>$expectedBaseUrl),
            $checkPath,
            $logoutPath,
            null
        );

        $ed = $builder->getEntityDescriptor();

        $this->assertInstanceOf('AerialShip\LightSaml\Model\Metadata\EntityDescriptor', $ed);

        $this->assertEquals($expectedEntityID, $ed->getEntityID());

        $arr = $ed->getAllSpSsoDescriptors();
        $this->assertInternalType('array', $arr);
        $this->assertCount(1, $arr);

        $sp = $ed->getFirstSpSsoDescriptor();
        $this->assertInstanceOf('AerialShip\LightSaml\Model\Metadata\SpSsoDescriptor', $sp);

        $arr = $sp->getKeyDescriptors();
        $this->assertInternalType('array', $arr);
        $this->assertCount(0, $arr);

        // SLO
        $arr = $sp->findSingleLogoutServices();
        $this->assertInternalType('array', $arr);
        $this->assertCount(2, $arr);
        $this->assertInstanceOf('AerialShip\LightSaml\Model\Metadata\Service\SingleLogoutService', $arr[0]);
        $this->assertInstanceOf('AerialShip\LightSaml\Model\Metadata\Service\SingleLogoutService', $arr[1]);
        $this->assertEquals($expectedBaseUrl.$logoutPath, $arr[0]->getLocation());
        $this->assertEquals($expectedBaseUrl.$logoutPath, $arr[1]->getLocation());

        $arr = $sp->findSingleLogoutServices(Bindings::SAML2_HTTP_REDIRECT);
        $this->assertInternalType('array', $arr);
        $this->assertCount(1, $arr);

        $arr = $sp->findSingleLogoutServices(Bindings::SAML2_HTTP_POST);
        $this->assertInternalType('array', $arr);
        $this->assertCount(1, $arr);

        // ACS
        $arr = $sp->findAssertionConsumerServices();
        $this->assertInternalType('array', $arr);
        $this->assertCount(2, $arr);
        $this->assertInstanceOf('AerialShip\LightSaml\Model\Metadata\Service\AssertionConsumerService', $arr[0]);
        $this->assertInstanceOf('AerialShip\LightSaml\Model\Metadata\Service\AssertionConsumerService', $arr[1]);
        $this->assertEquals($expectedBaseUrl.$checkPath, $arr[0]->getLocation());
        $this->assertEquals($expectedBaseUrl.$checkPath, $arr[1]->getLocation());

        $arr = $sp->findAssertionConsumerServices(Bindings::SAML2_HTTP_REDIRECT);
        $this->assertInternalType('array', $arr);
        $this->assertCount(1, $arr);

        $arr = $sp->findAssertionConsumerServices(Bindings::SAML2_HTTP_POST);
        $this->assertInternalType('array', $arr);
        $this->assertCount(1, $arr);
    }


    /**
     * @test
     */
    public function shouldBuildWithSigningEnabled()
    {
        $expectedCertificate = $this->createX509CertificateMock();
        $signingProvider = $this->createSPSigningProviderStub(true, $expectedCertificate, null);
        $expectedEntityID = 'entityID';
        $expectedBaseUrl = 'http://site.com';
        $checkPath = '/check_path';
        $logoutPath = '/logout_path';

        $builder = new SpEntityDescriptorBuilder(
            'idp',
            $signingProvider,
            array('entity_id'=>$expectedEntityID, 'base_url'=>$expectedBaseUrl),
            $checkPath,
            $logoutPath,
            null
        );

        $ed = $builder->getEntityDescriptor();

        $sp = $ed->getFirstSpSsoDescriptor();
        $this->assertInstanceOf('AerialShip\LightSaml\Model\Metadata\SpSsoDescriptor', $sp);

        $arr = $sp->getKeyDescriptors();
        $this->assertInternalType('array', $arr);
        $this->assertCount(1, $arr);
        $this->assertEquals($expectedCertificate, $arr[0]->getCertificate());
    }


    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Request not set
     */
    public function shouldThrowWhenBuildIsCalledWithoutSetBaseUrlAndRequest()
    {
        $signingProvider = $this->createSPSigningProviderStub(false, null, null);
        $expectedEntityID = 'entityID';
        $checkPath = '/check_path';
        $logoutPath = '/logout_path';

        $builder = new SpEntityDescriptorBuilder(
            'idp',
            $signingProvider,
            array('entity_id'=>$expectedEntityID),
            $checkPath,
            $logoutPath,
            $this->createHttpUtilsMock()
        );

        $builder->getEntityDescriptor();
    }


    /**
     * @test
     */
    public function shouldBuildWithoutBaseUrlAndWithHttpUtils()
    {
        $signingProvider = $this->createSPSigningProviderStub(false, null, null);
        $expectedEntityID = 'entityID';
        $checkPath = '/check_path';
        $logoutPath = '/logout_path';
        $httpUtils = $this->createHttpUtilsStub($expectedLocation = 'generated_location');

        $builder = new SpEntityDescriptorBuilder(
            'idp',
            $signingProvider,
            array('entity_id'=>$expectedEntityID),
            $checkPath,
            $logoutPath,
            $httpUtils
        );

        $builder->setRequest($this->createRequestMock());
        $ed = $builder->getEntityDescriptor();

        $this->assertInstanceOf('AerialShip\LightSaml\Model\Metadata\EntityDescriptor', $ed);

        $this->assertEquals($expectedEntityID, $ed->getEntityID());

        $arr = $ed->getAllSpSsoDescriptors();
        $this->assertInternalType('array', $arr);
        $this->assertCount(1, $arr);

        $sp = $ed->getFirstSpSsoDescriptor();
        $this->assertInstanceOf('AerialShip\LightSaml\Model\Metadata\SpSsoDescriptor', $sp);

        $arr = $sp->getKeyDescriptors();
        $this->assertInternalType('array', $arr);
        $this->assertCount(0, $arr);

        // SLO
        $arr = $sp->findSingleLogoutServices();
        $this->assertInternalType('array', $arr);
        $this->assertCount(2, $arr);
        $this->assertInstanceOf('AerialShip\LightSaml\Model\Metadata\Service\SingleLogoutService', $arr[0]);
        $this->assertInstanceOf('AerialShip\LightSaml\Model\Metadata\Service\SingleLogoutService', $arr[1]);
        $this->assertEquals($expectedLocation, $arr[0]->getLocation());
        $this->assertEquals($expectedLocation, $arr[1]->getLocation());

        $arr = $sp->findSingleLogoutServices(Bindings::SAML2_HTTP_REDIRECT);
        $this->assertInternalType('array', $arr);
        $this->assertCount(1, $arr);

        $arr = $sp->findSingleLogoutServices(Bindings::SAML2_HTTP_POST);
        $this->assertInternalType('array', $arr);
        $this->assertCount(1, $arr);

        // ACS
        $arr = $sp->findAssertionConsumerServices();
        $this->assertInternalType('array', $arr);
        $this->assertCount(2, $arr);
        $this->assertInstanceOf('AerialShip\LightSaml\Model\Metadata\Service\AssertionConsumerService', $arr[0]);
        $this->assertInstanceOf('AerialShip\LightSaml\Model\Metadata\Service\AssertionConsumerService', $arr[1]);
        $this->assertEquals($expectedLocation, $arr[0]->getLocation());
        $this->assertEquals($expectedLocation, $arr[1]->getLocation());

        $arr = $sp->findAssertionConsumerServices(Bindings::SAML2_HTTP_REDIRECT);
        $this->assertInternalType('array', $arr);
        $this->assertCount(1, $arr);

        $arr = $sp->findAssertionConsumerServices(Bindings::SAML2_HTTP_POST);
        $this->assertInternalType('array', $arr);
        $this->assertCount(1, $arr);
    }



    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SPSigningProviderInterface
     */
    protected function createSPSigningProviderMock()
    {
        return $this->getMock('AerialShip\SamlSPBundle\Config\SPSigningProviderInterface');
    }


    /**
     * @param $enabled
     * @param $certificate
     * @param $key
     * @return SPSigningProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createSPSigningProviderStub($enabled, $certificate, $key)
    {
        $result = $this->createSPSigningProviderMock();
        $result->expects($this->any())->method('isEnabled')->will($this->returnValue($enabled));
        if ($enabled) {
            $result->expects($this->any())->method('getCertificate')->will($this->returnValue($certificate));
            $result->expects($this->any())->method('getPrivateKey')->will($this->returnValue($key));
        } else {
            $result->expects($this->never())->method('getCertificate');
            $result->expects($this->never())->method('getPrivateKey');
        }
        return $result;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\Security\Http\HttpUtils
     */
    protected function createHttpUtilsMock()
    {
        return $this->getMock('Symfony\Component\Security\Http\HttpUtils');
    }

    /**
     * @param $generateUriReturn
     * @return \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\Security\Http\HttpUtils
     */
    protected function createHttpUtilsStub($generateUriReturn)
    {
        $result = $this->createHttpUtilsMock();
        $result->expects($this->any())
                ->method('generateUri')
                ->will($this->returnValue($generateUriReturn));
        return $result;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\AerialShip\LightSaml\Security\X509Certificate
     */
    protected function createX509CertificateMock()
    {
        return $this->getMock('AerialShip\LightSaml\Security\X509Certificate');
    }


    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\HttpFoundation\Request
     */
    private function createRequestMock()
    {
        return $this->getMock('Symfony\Component\HttpFoundation\Request', array(), array(), '', false, false);
    }

} 