<?php

declare(strict_types=1);

namespace Incognito\UnitTests\CognitoClient;

use Incognito\CognitoClient\CognitoCredentials;
use PHPUnit\Framework\TestCase;

class CognitoCredentialsTest extends TestCase
{
    public function testConstruct(): void
    {
        $sut = new CognitoCredentials(
            'someCognitoClientId',
            'someCognitoClientSecret',
            'someCognitoUserPoolId'
        );

        static::assertInstanceOf(
            CognitoCredentials::class,
            $sut
        );
    }

    public function testGetClientId(): void
    {
        $sut = new CognitoCredentials(
            'someCognitoClientId',
            'someCognitoClientSecret',
            'someCognitoUserPoolId'
        );

        static::assertEquals(
            'someCognitoClientId',
            $sut->getClientId()
        );
    }

    public function testGetClientSecret(): void
    {
        $sut = new CognitoCredentials(
            'someCognitoClientId',
            'someCognitoClientSecret',
            'someCognitoUserPoolId'
        );

        static::assertEquals(
            'someCognitoClientSecret',
            $sut->getClientSecret()
        );
    }

    public function testGetUserPoolId(): void
    {
        $sut = new CognitoCredentials(
            'someCognitoClientId',
            'someCognitoClientSecret',
            'someCognitoUserPoolId'
        );

        static::assertEquals(
            'someCognitoUserPoolId',
            $sut->getUserPoolId()
        );
    }

    public function testGetSecretHashForUsername(): void
    {
        $sut = new CognitoCredentials(
            'someCognitoClientId',
            'someCognitoClientSecret',
            'someCognitoUserPoolId'
        );

        static::assertEquals(
            'leH+ElshqALx+Oe0f20zk2dIr98jj0uwXwuKcQiQa0A=',
            $sut->getSecretHashForUsername('some-username')
        );
    }
}
