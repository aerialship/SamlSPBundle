<?php

namespace AerialShip\SamlSPBundle\Tests\Security\Core\Authentication\Provider;

use AerialShip\SamlSPBundle\Security\Core\Authentication\Provider\SamlSpAuthenticationProvider;
use AerialShip\SamlSPBundle\Security\Core\Authentication\Token\SamlSpToken;
use AerialShip\SamlSPBundle\Tests\Bridge\SamlSpInfoHelper;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class SamlSpAuthenticationProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function couldBeConstructedWithProviderKey()
    {
        new SamlSpAuthenticationProvider('key');
    }


    /**
     * @test
     */
    public function couldBeConstructedWithUserManagerAndUserChecker()
    {
        new SamlSpAuthenticationProvider(
            'main',
            $this->createUserManagerMock(),
            $this->createUserCheckerMock()
        );
    }

    /**
     * @test
     */
    public function couldBeConstructedWithUserManagerAndUserCheckerAndCreateUserIfNotExist()
    {
        new SamlSpAuthenticationProvider(
            'main',
            $this->createUserManagerMock(),
            $this->createUserCheckerMock(),
            true
        );
    }


    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function throwIfTryConstructWithUserManagerButWithoutUserChecker()
    {
        new SamlSpAuthenticationProvider(
            'main',
            $this->createUserManagerMock(),
            null,
            false
        );
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function throwIfTryConstructWithCreateIfNotExistSetTrueButWithoutUserManager()
    {
        new SamlSpAuthenticationProvider(
            'main',
            null,
            null,
            true
        );
    }


    /**
     * @test
     */
    public function shouldSupportSamlSpToken()
    {
        $providerKey = 'main';
        $authProvider = new SamlSpAuthenticationProvider($providerKey);
        $this->assertTrue($authProvider->supports(new SamlSpToken($providerKey)));
    }

    /**
     * @test
     */
    public function shouldNotSupportNonOpenIdToken()
    {
        $authProvider = new SamlSpAuthenticationProvider('main');

        /** @var $nonOpenIdToken TokenInterface */
        $nonOpenIdToken = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');

        $this->assertFalse($authProvider->supports($nonOpenIdToken));
        $this->assertNull($authProvider->authenticate($nonOpenIdToken));
    }


    /**
     * @test
     */
    public function shouldNotSupportSamlSpTokenIfProviderKeyDiffers()
    {
        $token = new SamlSpToken('the_other_provider_key');
        $authProvider = new SamlSpAuthenticationProvider('the_provider_key');
        $this->assertFalse($authProvider->supports($token));
    }


    /**
     * @test
     */
    public function shouldCreateAuthenticatedTokenUsingUserAndHisRolesFromToken()
    {
        $samlSpInfoHelper = new SamlSpInfoHelper();

        $providerKey = 'main';
        $expectedSamlSpInfo = $samlSpInfoHelper->getSamlSpInfo();

        $expectedUserMock = $this->createUserMock();
        $expectedUserMock
                ->expects($this->any())
                ->method('getRoles')
                ->will($this->returnValue(array('foo', 'bar')))
        ;

        $authProvider = new SamlSpAuthenticationProvider(
            $providerKey,
            $userManager = null,
            $this->createUserCheckerMock()
        );

        $token = new SamlSpToken($providerKey);
        $token->setUser($expectedUserMock);
        $token->setSamlSpInfo($expectedSamlSpInfo);

        /** @var  $authenticatedToken SamlSpToken */
        $authenticatedToken = $authProvider->authenticate($token);

        $this->assertInstanceOf('AerialShip\SamlSPBundle\Security\Core\Authentication\Token\SamlSpToken', $authenticatedToken);
        $this->assertNotSame($token, $authenticatedToken);
        $this->assertTrue($authenticatedToken->isAuthenticated());
        $this->assertEquals($expectedSamlSpInfo, $authenticatedToken->getSamlSpInfo());
        $this->assertSame($authenticatedToken->getUser(), $expectedUserMock);

        $roles = $authenticatedToken->getRoles();
        $this->assertInternalType('array', $roles);
        $this->assertCount(2, $roles);

        $this->assertEquals('foo', $roles[0]->getRole());
        $this->assertEquals('bar', $roles[1]->getRole());
    }




    /**
     * @test
     */
    public function shouldCreateAuthenticatedTokenUsingUserFromTokenAndCallPostAuthCheck()
    {
        $samlSpInfoHelper = new SamlSpInfoHelper();

        $providerKey = 'main';
        $expectedSamlSpInfo = $samlSpInfoHelper->getSamlSpInfo();

        $userMock = $this->createUserMock();
        $userMock
                ->expects($this->any())
                ->method('getRoles')
                ->will($this->returnValue(array()))
        ;

        $userCheckerMock = $this->createUserCheckerMock();
        $userCheckerMock
                ->expects($this->once())
                ->method('checkPostAuth')
                ->with($userMock)
        ;

        $authProvider = new SamlSpAuthenticationProvider(
            $providerKey,
            $this->createUserManagerMock(),
            $userCheckerMock
        );

        $token = new SamlSpToken($providerKey);
        $token->setUser($userMock);
        $token->setSamlSpInfo($expectedSamlSpInfo);

        $authenticatedToken = $authProvider->authenticate($token);

        $this->assertInstanceOf('AerialShip\SamlSPBundle\Security\Core\Authentication\Token\SamlSpToken', $authenticatedToken);
        $this->assertSame($authenticatedToken->getUser(), $userMock);
    }


    /**
     * @test
     */
    public function shouldCreateAuthenticatedTokenUsingIdentityIfUserManagerNotSet()
    {
        $samlSpInfoHelper = new SamlSpInfoHelper();

        $expectedProviderKey = 'the_provider_key';
        $expectedSamlSpInfo = $samlSpInfoHelper->getSamlSpInfo();
        $expectedUsername = $expectedSamlSpInfo->getNameID()->getValue();

        $authProvider = new SamlSpAuthenticationProvider($expectedProviderKey);

        $token = new SamlSpToken($expectedProviderKey);
        $token->setUser('');
        $token->setSamlSpInfo($expectedSamlSpInfo);

        /** @var $authenticatedToken SamlSpToken */
        $authenticatedToken = $authProvider->authenticate($token);

        $this->assertInstanceOf('AerialShip\SamlSPBundle\Security\Core\Authentication\Token\SamlSpToken', $authenticatedToken);
        $this->assertNotSame($token, $authenticatedToken);
        $this->assertTrue($authenticatedToken->isAuthenticated());
        $this->assertEquals($expectedSamlSpInfo, $authenticatedToken->getSamlSpInfo());
        $this->assertEquals($expectedProviderKey, $authenticatedToken->getProviderKey());

        /** @var $user \Symfony\Component\Security\Core\User\User */
        $user = $authenticatedToken->getUser();
        $this->assertInstanceOf('Symfony\Component\Security\Core\User\User', $user);
        $this->assertEquals($expectedUsername, $user->getUsername());

        $roles = $authenticatedToken->getRoles();
        $this->assertInternalType('array', $roles);
        $this->assertCount(1, $roles);
        $this->assertEquals('ROLE_USER', $roles[0]->getRole());
    }


    /**
     * @test
     */
    public function shouldCreateAuthenticatedTokenUsingUserManagerAndSearchBySamlSpInfo()
    {
        $samlSpInfoHelper = new SamlSpInfoHelper();

        $expectedProviderKey = 'the_provider_key';
        $expectedSamlSpInfo = $samlSpInfoHelper->getSamlSpInfo();

        $expectedUserMock = $this->createUserMock();
        $expectedUserMock
                ->expects($this->any())
                ->method('getRoles')
                ->will($this->returnValue(array('foo', 'bar')))
        ;

        $userManagerMock = $this->createUserManagerMock();
        $userManagerMock
                ->expects($this->once())
                ->method('loadUserBySamlInfo')
                ->with($expectedSamlSpInfo)
                ->will($this->returnValue($expectedUserMock))
        ;

        $authProvider = new SamlSpAuthenticationProvider(
            $expectedProviderKey,
            $userManagerMock,
            $this->createUserCheckerMock()
        );

        $token = new SamlSpToken($expectedProviderKey);
        $token->setUser('');
        $token->setSamlSpInfo($expectedSamlSpInfo);

        /** @var $authenticatedToken SamlSpToken */
        $authenticatedToken = $authProvider->authenticate($token);

        $this->assertInstanceOf('AerialShip\SamlSPBundle\Security\Core\Authentication\Token\SamlSpToken', $authenticatedToken);
        $this->assertNotSame($token, $authenticatedToken);
        $this->assertTrue($authenticatedToken->isAuthenticated());
        $this->assertEquals($expectedSamlSpInfo, $authenticatedToken->getSamlSpInfo());
        $this->assertEquals($expectedProviderKey, $authenticatedToken->getProviderKey());
        $this->assertEquals($expectedUserMock, $authenticatedToken->getUser());

        $roles = $authenticatedToken->getRoles();
        $this->assertInternalType('array', $roles);
        $this->assertCount(2, $roles);

        $this->assertEquals('foo', $roles[0]->getRole());
        $this->assertEquals('bar', $roles[1]->getRole());
    }


    /**
     * @test
     *
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationServiceException
     * @expectedExceptionMessage User provider did not return an implementation of user interface.
     */
    public function throwIfUserManagerReturnNonUserInstance()
    {
        $samlSpInfoHelper = new SamlSpInfoHelper();

        $providerKey = 'main';
        $expectedSamlSpInfo = $samlSpInfoHelper->getSamlSpInfo();

        $userProviderMock = $this->createUserManagerMock();
        $userProviderMock
                ->expects($this->once())
                ->method('loadUserBySamlInfo')
                ->with($expectedSamlSpInfo)
                ->will($this->returnValue('not-valid-user-instance'))
        ;

        $authProvider = new SamlSpAuthenticationProvider(
            $providerKey,
            $userProviderMock,
            $this->createUserCheckerMock()
        );

        $token = new SamlSpToken($providerKey);
        $token->setUser('');
        $token->setSamlSpInfo($expectedSamlSpInfo);

        $authProvider->authenticate($token);
    }


    /**
     * @test
     *
     * @expectedException \Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     * @expectedExceptionMessage Cannot find user by saml sp info
     */
    public function shouldNotCreateUserIfCreateIfNotExistParamIsNotSet()
    {
        $samlSpInfoHelper = new SamlSpInfoHelper();

        $providerKey = 'main';
        $expectedSamlSpInfo = $samlSpInfoHelper->getSamlSpInfo();

        $userManagerMock = $this->createUserManagerMock();
        $userManagerMock
                ->expects($this->once())
                ->method('loadUserBySamlInfo')
                ->with($expectedSamlSpInfo)
                ->will($this->throwException(new UsernameNotFoundException('Cannot find user by saml sp info')))
        ;
        $userManagerMock
                ->expects($this->never())
                ->method('createUserFromSamlInfo')
        ;

        $authProvider = new SamlSpAuthenticationProvider(
            $providerKey,
            $userManagerMock,
            $this->createUserCheckerMock(),
            $createIfNotExist = false
        );

        $token = new SamlSpToken($providerKey);
        $token->setUser('');
        $token->setSamlSpInfo($expectedSamlSpInfo);

        $authProvider->authenticate($token);
    }



    /**
     * @test
     */
    public function shouldCreateAuthenticatedTokenUsingUserManagerCreateFromSamlSpInfoMethod()
    {
        $samlSpInfoHelper = new SamlSpInfoHelper();

        $expectedProviderKey = 'main';
        $expectedSamlSpInfo = $samlSpInfoHelper->getSamlSpInfo();

        $expectedUserMock = $this->createUserMock();
        $expectedUserMock
                ->expects($this->any())
                ->method('getRoles')
                ->will($this->returnValue(array('foo', 'bar')))
        ;

        $userManagerMock = $this->createUserManagerMock();
        $userManagerMock
                ->expects($this->once())
                ->method('loadUserBySamlInfo')
                ->with($expectedSamlSpInfo)
                ->will($this->throwException(new UsernameNotFoundException('Cannot find user by saml sp info')))
        ;
        $userManagerMock
                ->expects($this->once())
                ->method('createUserFromSamlInfo')
                ->with($expectedSamlSpInfo)
                ->will($this->returnValue($expectedUserMock))
        ;

        $authProvider = new SamlSpAuthenticationProvider(
            $expectedProviderKey,
            $userManagerMock,
            $this->createUserCheckerMock(),
            $createIfNotExist = true
        );

        $token = new SamlSpToken($expectedProviderKey);
        $token->setUser('');
        $token->setSamlSpInfo($expectedSamlSpInfo);

        /** @var $authenticatedToken SamlSpToken */
        $authenticatedToken = $authProvider->authenticate($token);

        $this->assertInstanceOf('AerialShip\SamlSPBundle\Security\Core\Authentication\Token\SamlSpToken', $authenticatedToken);
        $this->assertNotSame($token, $authenticatedToken);
        $this->assertTrue($authenticatedToken->isAuthenticated());
        $this->assertEquals($expectedSamlSpInfo, $authenticatedToken->getSamlSpInfo());
        $this->assertEquals($expectedProviderKey, $authenticatedToken->getProviderKey());
        $this->assertEquals($expectedUserMock, $authenticatedToken->getUser());

        $roles = $authenticatedToken->getRoles();
        $this->assertInternalType('array', $roles);
        $this->assertCount(2, $roles);

        $this->assertEquals('foo', $roles[0]->getRole());
        $this->assertEquals('bar', $roles[1]->getRole());
    }


    /**
     * @test
     *
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationServiceException
     * @expectedExceptionMessage User provider did not return an implementation of user interface.
     */
    public function throwIfUserManagerCreateNotUserInstance()
    {
        $samlSpInfoHelper = new SamlSpInfoHelper();

        $providerKey = 'main';
        $expectedSamlSpInfo = $samlSpInfoHelper->getSamlSpInfo();

        $userManagerMock = $this->createUserManagerMock();
        $userManagerMock
                ->expects($this->once())
                ->method('loadUserBySamlInfo')
                ->with($expectedSamlSpInfo)
                ->will($this->throwException(new UsernameNotFoundException('Cannot find user by saml sp info')))
        ;
        $userManagerMock
                ->expects($this->once())
                ->method('createUserFromSamlInfo')
                ->with($expectedSamlSpInfo)
                ->will($this->returnValue('not-a-user-instance'))
        ;

        $authProvider = new SamlSpAuthenticationProvider(
            $providerKey,
            $userManagerMock,
            $this->createUserCheckerMock(),
            $createIfNotExist = true
        );

        $token = new SamlSpToken($providerKey);
        $token->setUser('');
        $token->setSamlSpInfo($expectedSamlSpInfo);

        $authProvider->authenticate($token);
    }



    /**
     * @test
     */
    public function shouldWrapAnyThrownExceptionsAsAuthenticatedServiceException()
    {
        $samlSpInfoHelper = new SamlSpInfoHelper();
        $providerKey = 'main';
        $expectedSamlSpInfo = $samlSpInfoHelper->getSamlSpInfo();
        $expectedPreviousException = new \Exception(
            $expectedMessage = 'Something goes wrong',
            $expectedCode = 21
        );

        $userProviderMock = $this->createUserManagerMock();
        $userProviderMock
                ->expects($this->once())
                ->method('loadUserBySamlInfo')
                ->will($this->throwException($expectedPreviousException))
        ;

        $authProvider = new SamlSpAuthenticationProvider(
            $providerKey,
            $userProviderMock,
            $this->createUserCheckerMock()
        );

        $token = new SamlSpToken($providerKey);
        $token->setUser('');
        $token->setSamlSpInfo($expectedSamlSpInfo);

        try {
            $authProvider->authenticate($token);
        } catch (AuthenticationServiceException $e) {
            $this->assertSame($expectedPreviousException, $e->getPrevious(), $e->getPrevious());
            $this->assertEquals($expectedMessage, $e->getMessage());
            $this->assertEquals($expectedCode, $e->getCode());
            $this->assertNull($e->getToken());

            return;
        }

        $this->fail('Expected exception: AuthenticationServiceException was not thrown');
    }



    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\AerialShip\SamlSPBundle\Security\Core\User\UserManagerInterface
     */
    protected function createUserManagerMock()
    {
        return $this->getMock('AerialShip\SamlSPBundle\Security\Core\User\UserManagerInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\Security\Core\User\UserCheckerInterface
     */
    protected function createUserCheckerMock()
    {
        return $this->getMock('Symfony\Component\Security\Core\User\UserCheckerInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\Security\Core\User\UserInterface
     */
    protected function createUserMock()
    {
        return $this->getMock('Symfony\Component\Security\Core\User\UserInterface');
    }
}
