<?php

namespace AerialShip\SamlSPBundle\Tests\Bridge;


use AerialShip\SamlSPBundle\Bridge\SamlSpInfo;

class SamlSpInfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementSerializableInterface()
    {
        $rc = new \ReflectionClass('AerialShip\SamlSPBundle\Bridge\SamlSpInfo');
        $this->assertTrue($rc->implementsInterface('Serializable'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithIdpID()
    {
        new SamlSpInfo('idp');
    }

    /**
     * @test
     */
    public function couldBeConstructedWithNameID()
    {
        $helper = new SamlSpInfoHelper();
        new SamlSpInfo('idp', $helper->getNameID());
    }

    /**
     * @test
     */
    public function couldBeConstructedWithNameIDAndAttributes()
    {
        $helper = new SamlSpInfoHelper();
        new SamlSpInfo('idp', $helper->getNameID(), $helper->getAttributes());
    }

    /**
     * @test
     */
    public function couldBeConstructedWithNameIDAndAttributesAndAuthnStatement()
    {
        $helper = new SamlSpInfoHelper();
        new SamlSpInfo('idp', $helper->getNameID(), $helper->getAttributes(), $helper->getAuthnStatement());
    }


    /**
     * @test
     */
    public function shouldDeserialize()
    {
        $helper = new SamlSpInfoHelper();
        $expectedSamlSpInfo = $helper->getSamlSpInfo();

        $unserializedSamlSpInfo = unserialize(serialize($expectedSamlSpInfo));

        $this->assertEquals($expectedSamlSpInfo, $unserializedSamlSpInfo);
    }

    /**
     * @test
     */
    public function shouldAllowGetIdpIdSetInConstructor()
    {
        $expectedIDP = 'idp';
        $samlSpInfo = new SamlSpInfo($expectedIDP);

        $this->assertEquals($expectedIDP, $samlSpInfo->getAuthenticationServiceID());
    }

    /**
     * @test
     */
    public function shouldAllowGetNameIDSetInConstructor()
    {
        $helper = new SamlSpInfoHelper();
        $expectedNameID = $helper->getNameID();

        $samlSpInfo = new SamlSpInfo('idp', $expectedNameID);

        $this->assertEquals($expectedNameID, $samlSpInfo->getNameID());
    }


    /**
     * @test
     */
    public function shouldAllowGetAttributesSetInConstructor()
    {
        $helper = new SamlSpInfoHelper();
        $expectedAttributes = $helper->getAttributes();

        $samlSpInfo = new SamlSpInfo('idp', null, $expectedAttributes);

        $this->assertEquals($expectedAttributes, $samlSpInfo->getAttributes());
        $this->assertInternalType('array', $samlSpInfo->getAttributes());
        $this->assertCount(2, $samlSpInfo->getAttributes());
    }


    /**
     * @test
     */
    public function shouldAllowGetAuthnStatementSetInConstructor()
    {
        $helper = new SamlSpInfoHelper();
        $expectedAuthnStatement = $helper->getAuthnStatement();

        $samlSpInfo = new SamlSpInfo('idp', null, null, $expectedAuthnStatement);

        $this->assertEquals($expectedAuthnStatement, $samlSpInfo->getAuthnStatement());
    }

    /**
     * @test
     */
    public function shouldReturnEmptyArrayWhenNullAttributesSetInConstructor()
    {
        $samlSpInfo = new SamlSpInfo('idp');

        $this->assertEquals(array(), $samlSpInfo->getAttributes());
    }

} 