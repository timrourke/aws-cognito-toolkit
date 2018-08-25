<?php

declare(strict_types=1);

namespace Incognito\UnitTests\Token;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Incognito\Token\Keychain;
use Incognito\UnitTests\Cache\PsrCacheItemPoolStub;
use Incognito\UnitTests\Cache\PsrCacheItemStub;
use Incognito\UnitTests\Cache\CacheItemFactoryStub;
use Jose\Component\Core\JWKSet;
use PHPUnit\Framework\TestCase;

class KeychainTest extends TestCase
{
    public function testConstruct(): void
    {
        $keychain = new Keychain(
            $this->getGuzzleMock(),
            $this->getCacheItemPoolMock(),
            new CacheItemFactoryStub()
        );

        $this->assertInstanceOf(
            Keychain::class,
            $keychain
        );
    }

    public function testGetPublicKeyset(): void
    {
        $guzzleMock = $this->getGuzzleMock();
        $cacheItemPoolMock = $this->getCacheItemPoolMock();

        $cacheItemPoolMock->expects($this->any())
            ->method('getItem')
            ->with('cognito.jwt.public-keys')
            ->willReturn(
                new PsrCacheItemStub(
                    'cognito.jwt.public-keys',
                    $this->getRsaKeysetStub(),
                    true
                )
            );

        $keychain = new Keychain(
            $guzzleMock,
            $cacheItemPoolMock,
            new CacheItemFactoryStub()
        );

        $keyset = $keychain->getPublicKeyset();

        $this->assertEquals(
            (JWKSet::createFromKeyData($this->getRsaKeysetStub()))->jsonSerialize(),
            $keyset->jsonSerialize()
        );
    }

    public function testGetPublicKeysetWithColdCache(): void
    {
        $guzzleMock = $this->getGuzzleMock();
        $cacheItemPoolMock = $this->getCacheItemPoolMock();

        $cacheItemPoolMock->expects($this->any())
            ->method('getItem')
            ->with('cognito.jwt.public-keys')
            ->willReturn(
                new PsrCacheItemStub(
                    'cognito.jwt.public-keys',
                    null,
                    false
                )
            );

        $response = new Response(
            200,
            [],
            json_encode($this->getRsaKeysetStub())
        );

        $guzzleMock->expects($this->once())
            ->method('request')
            ->with('GET', '', [])
            ->willReturn($response);

        $keychain = new Keychain(
            $guzzleMock,
            $cacheItemPoolMock,
            new CacheItemFactoryStub()
        );

        $keyset = $keychain->getPublicKeyset();

        $this->assertEquals(
            (JWKSet::createFromKeyData($this->getRsaKeysetStub()))->jsonSerialize(),
            $keyset->jsonSerialize()
        );
    }

    private function getRsaKeysetStub(): array
    {
        return [
            "keys" => [
                [
                    "kty" => "RSA",
                    "alg" => "RS256",
                    "use" => "sig",
                    "kid" => "e27671d73a2605ccd454413c4c94e25b3f66cdea",
                    "n"   => "vmyoDT6ND_YJa1ItdvULuTJr2pw4MvN3Z5kmSiJBm9glVoakcDEBGF4b5crKiPW7WDh2PZ0_yXY9ikDaTux7hxtgUtmm96KjmdBn_FYwv3SlsBRnzZw1oAG-2OdjlFWvlx4rXOhAzZ04ngPb3ELywwtKoO90hCy2DrNOMMSCuSu8zrFLw5oREawPcUFEQReipy_KRFf02VxFbK4Tj2FHVdBPPLW3W1KJD4S-NNwPnoeDrI6zWMv7WWAeSLAT0hX36r5FM9dM2uXTxPRCZzs-nqrUiHxn4duFIGgzuxCVbyigDrnfsmHx-B5tG1m7ts74xwf2P_PJwNNJ8qRihMsS2Q",
                    "e"   => "AQAB"
                ],
            ],
        ];
    }

    private function getGuzzleMock()
    {
        $mock = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }

    private function getCacheItemPoolMock()
    {
        $mock = $this->getMockBuilder(PsrCacheItemPoolStub::class)
            ->getMock();

        return $mock;
    }
}
