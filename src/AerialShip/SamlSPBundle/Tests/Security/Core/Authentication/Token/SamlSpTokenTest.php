<?php

namespace AerialShip\SamlSPBundle\Tests\Security\Core\Authentication\Token;

use AerialShip\SamlSPBundle\Security\Core\Authentication\Token\SamlSpToken;
use AerialShip\SamlSPBundle\Tests\Bridge\SamlSpInfoHelper;

class SamlSpTokenTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubclassOfAbstractToken()
    {
        $rc = new \ReflectionClass('AerialShip\SamlSPBundle\Security\Core\Authentication\Token\SamlSpToken');
        $this->assertTrue($rc->isSubclassOf('Symfony\Component\Security\Core\Authentication\Token\AbstractToken'));
    }


    /**
     * @test
     */
    public function couldBeConstructedWithProviderKey()
    {
        new SamlSpToken('key');
    }


    /**
     * @test
     */
    public function shouldReturnGetProviderKeyAsSetInConstructor()
    {
        $expectedProviderKey = 'some_provider_key';
        $token = new SamlSpToken($expectedProviderKey);
        $this->assertEquals($expectedProviderKey, $token->getProviderKey());
    }


    /**
     * @test
     */
    public function shouldReturnSetSamlSpInfo()
    {
        $samlSpInfoHelper = new SamlSpInfoHelper();
        $token = new SamlSpToken('key');
        $expectedSamlSpInfo = $samlSpInfoHelper->getSamlSpInfo();
        $token->setSamlSpInfo($expectedSamlSpInfo);
        $this->assertEquals($expectedSamlSpInfo, $token->getSamlSpInfo());
    }


    /**
     * @test
     */
    public function shouldUnserializeProviderKey()
    {
        $expectedProviderKey = 'some_provider_key';
        $token = new SamlSpToken($expectedProviderKey);

        $token = unserialize(serialize($token));

        $this->assertEquals($expectedProviderKey, $token->getProviderKey());

    }


    /**
     * @test
     */
    public function shouldUnserializeSamlSpInfo()
    {
        $samlSpInfoHelper = new SamlSpInfoHelper();
        $token = new SamlSpToken('key');
        $expectedSamlSpInfo = $samlSpInfoHelper->getSamlSpInfo();
        $token->setSamlSpInfo($expectedSamlSpInfo);

        $token = unserialize(serialize($token));

        $this->assertEquals($expectedSamlSpInfo, $token->getSamlSpInfo());
    }


    /**
     * @test
     */
    public function shouldCopyNameIDToAttributes()
    {
        $samlSpInfoHelper = new SamlSpInfoHelper();
        $token = new SamlSpToken('key');
        $expectedSamlSpInfo = $samlSpInfoHelper->getSamlSpInfo();
        $token->setSamlSpInfo($expectedSamlSpInfo);

        $this->assertTrue($token->hasAttribute(SamlSpToken::ATTRIBUTE_NAME_ID));
        $this->assertEquals($expectedSamlSpInfo->getNameID()->getValue(), $token->getAttribute(SamlSpToken::ATTRIBUTE_NAME_ID));
    }


    /**
     * @test
     */
    public function shouldCopyNameIDFormatToAttributes()
    {
        $samlSpInfoHelper = new SamlSpInfoHelper();
        $token = new SamlSpToken('key');
        $expectedSamlSpInfo = $samlSpInfoHelper->getSamlSpInfo();
        $token->setSamlSpInfo($expectedSamlSpInfo);

        $this->assertTrue($token->hasAttribute(SamlSpToken::ATTRIBUTE_NAME_ID_FORMAT));
        $this->assertEquals($expectedSamlSpInfo->getNameID()->getFormat(), $token->getAttribute(SamlSpToken::ATTRIBUTE_NAME_ID_FORMAT));
    }

    /**
     * @test
     */
    public function shouldCopySessionIndexToAttributes()
    {
        $samlSpInfoHelper = new SamlSpInfoHelper();
        $token = new SamlSpToken('key');
        $expectedSamlSpInfo = $samlSpInfoHelper->getSamlSpInfo();
        $token->setSamlSpInfo($expectedSamlSpInfo);

        $this->assertTrue($token->hasAttribute(SamlSpToken::ATTRIBUTE_SESSION_INDEX));
        $this->assertEquals($expectedSamlSpInfo->getAuthnStatement()->getSessionIndex(), $token->getAttribute(SamlSpToken::ATTRIBUTE_SESSION_INDEX));
    }


    /**
     * @test
     */
    public function shouldCopySamlAttributesToAttributes()
    {
        $samlSpInfoHelper = new SamlSpInfoHelper();
        $token = new SamlSpToken('key');
        $expectedSamlSpInfo = $samlSpInfoHelper->getSamlSpInfo();
        $token->setSamlSpInfo($expectedSamlSpInfo);

        $this->assertTrue($token->hasAttribute('a'));
        $this->assertEquals(1, $token->getAttribute('a'));

        $this->assertTrue($token->hasAttribute('b'));
        $this->assertEquals(array(2,3), $token->getAttribute('b'));
    }
} 
