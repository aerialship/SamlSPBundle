<?php

namespace AerialShip\SamlSPBundle\Tests\Security\Http;

use AerialShip\LightSaml\Model\Assertion\Attribute;
use AerialShip\LightSaml\Model\Assertion\AuthnStatement;
use AerialShip\LightSaml\Model\Assertion\NameID;
use AerialShip\SamlSPBundle\Bridge\SamlSpInfo;
use AerialShip\SamlSPBundle\RelyingParty\RelyingPartyInterface;
use AerialShip\SamlSPBundle\Security\Core\Authentication\Token\SamlSpToken;
use AerialShip\SamlSPBundle\Security\Http\Firewall\SamlSpAuthenticationListener;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface;


class SamlSpAuthenticationListenerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function shouldBeConstructedWithRequiredSetOfArguments()
    {
        new SamlSpAuthenticationListener(
            $this->createSecurityContextMock(),
            $this->createAuthenticationManagerMock(),
            $this->createSessionAuthenticationStrategyMock(),
            $this->createHttpUtilsMock(),
            'providerKey',
            $this->createAuthenticationSuccessHandlerMock(),
            $this->createAuthenticationFailureHandlerMock(),
            $options = array()
        );
    }

    /**
     * @test
     * @expectedException \AerialShip\SamlSPBundle\Error\RelyingPartyNotSetException
     */
    public function throwIfRelyingPartyNotSet()
    {
        $requestMock = $this->createRequestStub(
            $hasSessionReturn = true,
            $hasPreviousSessionReturn = true,
            $duplicatedRequestMock = $this->createRequestMock()
        );
        $duplicatedRequestMock->attributes = new ParameterBag();

        $eventMock = $this->createGetResponseEventStub($requestMock);

        $listener = new SamlSpAuthenticationListener(
            $this->createSecurityContextMock(),
            $this->createAuthenticationManagerMock(),
            $this->createSessionAuthenticationStrategyMock(),
            $this->createHttpUtilsMock(),
            'providerKey',
            $this->createAuthenticationSuccessHandlerMock(),
            $this->createAuthenticationFailureHandlerMock(),
            $options = array('require_previous_session'=>false)
        );

        $listener->handle($eventMock);
    }



    /**
     * @test
     */
    public function shouldDuplicateRequestAndPassItToRelyingPartyManageMethod()
    {
        $requestMock = $this->createRequestStub(
            $hasSessionReturn = true,
            $hasPreviousSessionReturn = true,
            $duplicatedRequestMock = $this->createRequestMock()
        );
        $duplicatedRequestMock->attributes = new ParameterBag();

        $relyingPartyMock = $this->createRelyingPartyMock();
        $relyingPartyMock
                ->expects($this->any())
                ->method('supports')
                ->will($this->returnValue(true))
        ;
        $relyingPartyMock
                ->expects($this->once())
                ->method('manage')
                ->with($this->equalTo($duplicatedRequestMock))
                ->will($this->returnValue(new RedirectResponse('http://example.com/saml/idp')))
        ;

        $eventMock = $this->createGetResponseEventStub($requestMock);

        $listener = new SamlSpAuthenticationListener(
            $this->createSecurityContextMock(),
            $this->createAuthenticationManagerMock(),
            $this->createSessionAuthenticationStrategyMock(),
            $this->createHttpUtilsStub($checkRequestPathReturn = true),
            'providerKey',
            $this->createAuthenticationSuccessHandlerMock(),
            $this->createAuthenticationFailureHandlerMock(),
            $options = array('require_previous_session'=>false)
        );

        $listener->setRelyingParty($relyingPartyMock);

        $listener->handle($eventMock);
    }


    /**
     * @test
     */
    public function shouldAddOptionsToDuplicatedRequest()
    {
        $requestOptions = array(
            'login_path' => '/saml/sp/login',
            'check_path' => '/saml/sp/check',
            'logout_path' => '/saml/sp/logout',
            'metadata_path' => '/saml/sp/FederationMetadata.xml',
            'discovery_path' => '/saml/sp/discovery',
            'failure_path' => 'saml/sp/failure'
        );

        $duplicatedRequestMock = $this->createRequestMock();
        $duplicatedRequestMock->attributes = new ParameterBag();

        $requestMock = $this->createRequestStub(
            $hasSessionReturn = true,
            $hasPreviousSessionReturn = true,
            $duplicateReturn = $duplicatedRequestMock
        );

        $relyingPartyMock = $this->createRelyingPartyStub(
            $supportsReturn = true,
            $manageReturn = new RedirectResponse('http://example.com/saml/idp')
        );

        $eventMock = $this->createGetResponseEventStub($requestMock);

        $listener = new SamlSpAuthenticationListener(
            $this->createSecurityContextMock(),
            $this->createAuthenticationManagerMock(),
            $this->createSessionAuthenticationStrategyMock(),
            $this->createHttpUtilsStub($checkRequestPathReturn = true),
            'providerKey',
            $this->createAuthenticationSuccessHandlerMock(),
            $this->createAuthenticationFailureHandlerMock(),
            $options = array_merge(array('require_previous_session'=>false), $requestOptions)
        );

        $listener->setRelyingParty($relyingPartyMock);

        $listener->handle($eventMock);

        $this->assertSame(
            $requestOptions,
            $duplicatedRequestMock->attributes->all()
        );
    }


    /**
     * @test
     */
    public function shouldCreateTokenFromIDPResponseAndPassItToAuthenticationManager()
    {
        $requestMock = $this->createRequestStub(
            $hasSessionReturn = true,
            $hasPreviousSessionReturn = true,
            $duplicateReturn = $this->createRequestMock(),
            $getSessionReturn = $this->createSessionMock()
        );
        $duplicateReturn->attributes = new ParameterBag();

        $nameID = new NameID();
        $nameID->setValue('name.id');
        $attribute1 = new Attribute();
        $attribute1->setName('common.name');
        $attribute1->setValues(array('my common name'));
        $authnStatement = new AuthnStatement();
        $authnStatement->setSessionIndex('1234567890');

        $relyingPartyMock = $this->createRelyingPartyStub(
            $supportsReturn = true,
            $manageReturnSamlSpInfo = new SamlSpInfo(
                'idp1',
                $nameID,
                array($attribute1),
                $authnStatement
            )
        );

        $httpUtilsStub = $this->createHttpUtilsStub(
            $checkRequestPathReturn = true,
            $createRedirectResponseReturn = new RedirectResponse('uri')
        );

        $testCase = $this;
        $authenticationManagerMock = $this->createAuthenticationManagerMock();
        $authenticationManagerMock
                ->expects($this->once())
                ->method('authenticate')
                ->with($this->isInstanceOf('AerialShip\SamlSPBundle\Security\Core\Authentication\Token\SamlSpToken'))
                ->will($this->returnCallback(function(SamlSpToken $actualToken) use ($testCase, $manageReturnSamlSpInfo) {
                    $samlInfo = $actualToken->getSamlSpInfo();
                    $testCase->assertNotNull($samlInfo);
                    $testCase->assertNotNull($samlInfo->getNameID());
                    $testCase->assertEquals('name.id', $samlInfo->getNameID()->getValue());
                    $testCase->assertNotNull($samlInfo->getAttributes());
                    $testCase->assertCount(1, $samlInfo->getAttributes());
                    $testCase->assertEquals($manageReturnSamlSpInfo, $actualToken->getSamlSpInfo());
                    return $actualToken;
                }))
        ;

        $eventMock = $this->createGetResponseEventStub($requestMock);

        $listener = new SamlSpAuthenticationListener(
            $this->createSecurityContextMock(),
            $authenticationManagerMock,
            $this->createSessionAuthenticationStrategyMock(),
            $httpUtilsStub,
            'providerKey',
            $this->createAuthenticationSuccessHandlerStub(),
            $this->createAuthenticationFailureHandlerMock(),
            $options = array()
        );

        $listener->setRelyingParty($relyingPartyMock);

        $listener->handle($eventMock);
    }


    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Request
     */
    private function createRequestMock()
    {
        return $this->getMock('Symfony\Component\HttpFoundation\Request', array(), array(), '', false, false);
    }

    /**
     * @param bool $hasSessionReturn
     * @param bool $hasPreviousSession
     * @param mixed $duplicateReturn
     * @param mixed $getSessionReturn
     * @return \PHPUnit_Framework_MockObject_MockObject|Request
     */
    private function createRequestStub($hasSessionReturn = null, $hasPreviousSession = null, $duplicateReturn = null, $getSessionReturn = null)
    {
        $requestMock = $this->createRequestMock();

        $requestMock
                ->expects($this->any())
                ->method('hasSession')
                ->will($this->returnValue($hasSessionReturn))
        ;
        $requestMock
                ->expects($this->any())
                ->method('hasPreviousSession')
                ->will($this->returnValue($hasPreviousSession))
        ;
        $requestMock
                ->expects($this->any())
                ->method('duplicate')
                ->will($this->returnValue($duplicateReturn))
        ;
        $requestMock
                ->expects($this->any())
                ->method('getSession')
                ->will($this->returnValue($getSessionReturn))
        ;

        return $requestMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|RelyingPartyInterface
     */
    private function createRelyingPartyMock()
    {
        return $this->getMock('AerialShip\SamlSPBundle\RelyingParty\RelyingPartyInterface');
    }

    private function createRelyingPartyStub($supportsReturn = null, $manageReturn = null)
    {
        $relyingPartyMock = $this->createRelyingPartyMock();

        $relyingPartyMock
                ->expects($this->any())
                ->method('supports')
                ->will($this->returnValue($supportsReturn))
        ;
        $relyingPartyMock
                ->expects($this->any())
                ->method('manage')
                ->will($this->returnValue($manageReturn))
        ;

        return $relyingPartyMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GetResponseEvent
     */
    private function createGetResponseEventMock()
    {
        return $this->getMock('Symfony\Component\HttpKernel\Event\GetResponseEvent', array(), array(), '', false);
    }

    /**
     * @param Request|null $request
     * @return \PHPUnit_Framework_MockObject_MockObject|GetResponseEvent
     */
    private function createGetResponseEventStub($request = null)
    {
        $getResponseEventMock = $this->createGetResponseEventMock();

        $getResponseEventMock
                ->expects($this->any())
                ->method('getRequest')
                ->will($this->returnValue($request))
        ;

        return $getResponseEventMock;
    }


    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SessionInterface
     */
    private function createSessionMock()
    {
        return $this->getMock('Symfony\Component\HttpFoundation\Session\SessionInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SecurityContextInterface
     */
    private function createSecurityContextMock()
    {
        return $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|AuthenticationManagerInterface
     */
    private function createAuthenticationManagerMock()
    {
        return $this->getMock('Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SessionAuthenticationStrategyInterface
     */
    private function createSessionAuthenticationStrategyMock()
    {
        return $this->getMock('Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|HttpUtils
     */
    private function createHttpUtilsMock()
    {
        return $this->getMock('Symfony\Component\Security\Http\HttpUtils');
    }

    /**
     * @param null $checkRequestPathResult
     * @param null $createRedirectResponseReturn
     * @return \PHPUnit_Framework_MockObject_MockObject|HttpUtils
     */
    private function createHttpUtilsStub($checkRequestPathResult = null, $createRedirectResponseReturn = null)
    {
        $httpUtilsMock = $this->createHttpUtilsMock();

        $httpUtilsMock
                ->expects($this->any())
                ->method('checkRequestPath')
                ->will($this->returnValue($checkRequestPathResult))
        ;
        $httpUtilsMock
                ->expects($this->any())
                ->method('createRedirectResponse')
                ->will($this->returnValue($createRedirectResponseReturn))
        ;

        return $httpUtilsMock;
    }


    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|AuthenticationSuccessHandlerInterface
     */
    private function createAuthenticationSuccessHandlerMock()
    {
        return $this->getMock('Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|AuthenticationSuccessHandlerInterface
     */
    protected function createAuthenticationSuccessHandlerStub()
    {
        $handlerMock = $this->createAuthenticationSuccessHandlerMock();

        $handlerMock
                ->expects($this->any())
                ->method('onAuthenticationSuccess')
                ->will($this->returnValue(new Response()))
        ;

        return $handlerMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|AuthenticationFailureHandlerInterface
     */
    private function createAuthenticationFailureHandlerMock()
    {
        return $this->getMock('Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface');
    }

} 