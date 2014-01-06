<?php

namespace AerialShip\SamlSPBundle\Tests\Config;


use AerialShip\LightSaml\Bindings;
use AerialShip\LightSaml\NameIDPolicy;
use AerialShip\SamlSPBundle\Config\SpMetaConfigProvider;

class SpMetaConfigProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeConstructed()
    {
        new SpMetaConfigProvider(array());
    }

    /**
     * @test
     */
    public function shouldImplementSpMetaProviderInterface()
    {
        $rc = new \ReflectionClass('AerialShip\SamlSPBundle\Config\SpMetaConfigProvider');
        $this->assertTrue($rc->implementsInterface('AerialShip\SamlSPBundle\Config\SpMetaProviderInterface'));
    }

    /**
     * @test
     */
    public function shouldReturnSpMeta()
    {
        $provider = new SpMetaConfigProvider(array());
        $this->assertInstanceOf('AerialShip\LightSaml\Meta\SpMeta', $provider->getSpMeta());
    }

    /**
     * @test
     */
    public function shouldResolvePersistentNameIDFormat()
    {
        $provider = new SpMetaConfigProvider(array('name_id_format'=>'persistent'));
        $spMeta = $provider->getSpMeta();
        $this->assertEquals(NameIDPolicy::PERSISTENT, $spMeta->getNameIdFormat());
    }

    /**
     * @test
     */
    public function shouldResolveTransientNameIDFormat()
    {
        $provider = new SpMetaConfigProvider(array('name_id_format'=>'transient'));
        $spMeta = $provider->getSpMeta();
        $this->assertEquals(NameIDPolicy::TRANSIENT, $spMeta->getNameIdFormat());
    }

    /**
     * @test
     */
    public function shouldResolvePostBinding()
    {
        $provider = new SpMetaConfigProvider(array('binding'=>array('authn_request'=>'post', 'logout_request'=>'post')));
        $spMeta = $provider->getSpMeta();
        $this->assertEquals(Bindings::SAML2_HTTP_POST, $spMeta->getAuthnRequestBinding());
        $this->assertEquals(Bindings::SAML2_HTTP_POST, $spMeta->getLogoutRequestBinding());
    }

    /**
     * @test
     */
    public function shouldResolveRedirectBinding()
    {
        $provider = new SpMetaConfigProvider(array('binding'=>array('authn_request'=>'redirect', 'logout_request'=>'redirect')));
        $spMeta = $provider->getSpMeta();
        $this->assertEquals(Bindings::SAML2_HTTP_REDIRECT, $spMeta->getAuthnRequestBinding());
        $this->assertEquals(Bindings::SAML2_HTTP_REDIRECT, $spMeta->getLogoutRequestBinding());
    }

} 