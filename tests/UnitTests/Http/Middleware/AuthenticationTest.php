<?php

declare(strict_types=1);

namespace Incognito\UnitTests\Http\Middleware;

use GuzzleHttp\Psr7\ServerRequest;
use Incognito\Http\InvalidTokenResponseFactory;
use Incognito\Http\Middleware\Authentication;
use Incognito\Token\TokenValidator;
use Incognito\Token\TokenValidatorFactory;
use Incognito\UnitTests\Token\TestUtility;
use Jose\Component\Signature\JWS;
use Jose\Component\Signature\Serializer\JWSSerializerManager;
use PHPUnit\Framework\TestCase;

class AuthenticationTest extends TestCase
{
    /**
     * @var \Jose\Component\Signature\Serializer\JWSSerializerManager
     */
    private JWSSerializerManager $serializer;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->serializer = TestUtility::getSerializerManager();
    }

    public function testConstruct(): void
    {
        $sut = $this->getAuthentication();

        static::assertInstanceOf(
            Authentication::class,
            $sut
        );
    }

    public function testProcess(): void
    {
        $sut = $this->getAuthentication();

        $validTokenString = $this->getValidTokenString();

        $authenticatedRequest = new ServerRequest(
            'GET',
            'localhost',
            [
                'Authorization' => 'Bearer ' . $validTokenString,
            ]
        );

        $handler = new PsrRequestHandlerStub();

        $response = $sut->process(
            $authenticatedRequest,
            $handler
        );

        static::assertEquals(
            200,
            $response->getStatusCode()
        );
    }

    public function testProcessWhenUnauthenticated(): void
    {
        $sut = $this->getAuthentication();

        $unauthenticatedRequest = new ServerRequest(
            'GET',
            'localhost'
        );

        $handler = new PsrRequestHandlerStub();

        $response = $sut->process(
            $unauthenticatedRequest,
            $handler
        );

        static::assertEquals(
            401,
            $response->getStatusCode()
        );
    }

    public function testProcessWhenAuthorizationHeaderMalformed(): void
    {
        $sut = $this->getAuthentication();

        $unauthenticatedRequest = new ServerRequest(
            'GET',
            'localhost',
            [
                'Authorization' => 'Token ' . $this->getValidTokenString(),
            ]
        );

        $handler = new PsrRequestHandlerStub();

        $response = $sut->process(
            $unauthenticatedRequest,
            $handler
        );

        static::assertEquals(
            401,
            $response->getStatusCode()
        );
    }

    public function testProcessWhenTokenInvalid(): void
    {
        $sut = $this->getAuthentication();

        $unauthenticatedRequest = new ServerRequest(
            'GET',
            'localhost',
            [
                'Authorization' => 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiYWRtaW4iOnRydWV9.TJVA95OrM7E2cBab30RMHrHDcEfxjoYZgeFONFh7HgQ',
            ]
        );

        $handler = new PsrRequestHandlerStub();

        $response = $sut->process(
            $unauthenticatedRequest,
            $handler
        );

        static::assertEquals(
            401,
            $response->getStatusCode()
        );
    }

    private function getTokenService(): TokenValidator
    {
        return TokenValidatorFactory::make(
            TestUtility::EXPECTED_AUDIENCE,
            TestUtility::getKeyset()
        );
    }

    private function getAuthentication(): Authentication
    {
        $service = $this->getTokenService();
        $authErrorResponseFactory = new InvalidTokenResponseFactory();

        return new Authentication(
            $service,
            $authErrorResponseFactory
        );
    }

    private function getValidTokenString(): string
    {
        $token = TestUtility::getJWS();

        return $this->serializeToken($token);
    }

    /**
     * @param \Jose\Component\Signature\JWS $token
     * @return string
     * @throws \Exception
     */
    private function serializeToken(JWS $token): string
    {
        return $this->serializer->serialize('jws_compact', $token);
    }
}
