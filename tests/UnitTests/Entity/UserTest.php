<?php

namespace Incognito\UnitTests\Entity;

use Assert\AssertionFailedException;
use Incognito\Entity\User;
use Incognito\Entity\Username;
use Incognito\Entity\UserStatus;
use Incognito\Entity\UserAttribute\UserAttribute;
use Incognito\Entity\UserAttribute\UserAttributeCollection;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testConstruct(): void
    {
        $sut = new User(
            new Username('foo')
        );

        $this->assertInstanceOf(
            User::class,
            $sut
        );
    }

    public function testConstructWithUserAttrs(): void
    {
        $attrs = new UserAttributeCollection([
            new UserAttribute('email', 'someone@somewhere.com'),
        ]);

        $sut = new User(
            new Username('foo'),
            $attrs
        );

        $this->assertEquals(
            $attrs->toArray(),
            $sut->getAttributes()
        );
    }

    public function testSetAttribute(): void
    {
        $newEmail = new UserAttribute('email', 'someotheremail@somewhere.com');

        $attrs = new UserAttributeCollection([
            new UserAttribute('email', 'someone@somewhere.com'),
        ]);

        $sut = new User(
            new Username('foo'),
            $attrs
        );

        $sut->setAttribute($newEmail);

        $this->assertEquals(
            $newEmail,
            $sut->getAttribute('email')
        );
    }

    public function testSetAttributeWhenNoAttributesExist(): void
    {
        $newEmail = new UserAttribute('email', 'someotheremail@somewhere.com');

        $sut = new User(
            new Username('foo')
        );

        $sut->setAttribute($newEmail);

        $this->assertEquals(
            $newEmail,
            $sut->getAttribute('email')
        );
    }

    public function testGetAttribute(): void
    {
        $givenName = new UserAttribute('given_name', 'Robin');

        $attrs = new UserAttributeCollection([
            $givenName
        ]);

        $sut = new User(
            new Username('foo'),
            $attrs
        );

        $this->assertEquals(
            $givenName,
            $sut->getAttribute('given_name')
        );
    }

    public function testGetAttributes(): void
    {
        $attrs = new UserAttributeCollection([
            new UserAttribute('email', 'someone@somewhere.com'),
        ]);

        $sut = new User(
            new Username('foo'),
            $attrs
        );

        $this->assertEquals(
            $attrs->toArray(),
            $sut->getAttributes()
        );
    }

    public function testGetAttributesWhenNoneExist(): void
    {
        $sut = new User(
            new Username('foo')
        );

        $this->assertEquals(
            [],
            $sut->getAttributes()
        );
    }

    public function testId(): void
    {
        $sut = new User(
            new Username('foo')
        );

        $this->assertEquals(
            '',
            $sut->id()
        );
    }

    public function testSetId(): void
    {
        $sut = new User(
            new Username('foo')
        );

        $sut->setId('some-id');

        $this->assertEquals(
            'some-id',
            $sut->id()
        );
    }

    public function testUsername(): void
    {
        $sut = new User(
            new Username('foo')
        );

        $this->assertEquals(
            'foo',
            $sut->username()
        );
    }

    public function testSetUsername(): void
    {
        $sut = new User(
            new Username('foo')
        );

        $sut->setUsername(
            new Username('bar')
        );

        $this->assertEquals(
            'bar',
            $sut->username()
        );
    }

    public function testCreatedAt(): void
    {
        $sut = new User(
            new Username('foo')
        );

        $this->assertEquals(
            null,
            $sut->createdAt()
        );
    }

    public function testSetCreatedAt(): void
    {
        $sut = new User(
            new Username('foo')
        );

        $date = new \DateTimeImmutable('now');

        $sut->setCreatedAt($date);

        $this->assertEquals(
            $date,
            $sut->createdAt()
        );
    }

    public function testSetCreatedAtThrowsWhenAlreadySet(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            'Invalid createdAt: user already has a "createdAt" date.'
        );

        $sut = new User(
            new Username('foo')
        );

        $date = new \DateTimeImmutable('now');

        $sut->setCreatedAt($date);
        $sut->setCreatedAt($date);
    }

    public function testUpdatedAt(): void
    {
        $sut = new User(
            new Username('foo')
        );

        $this->assertEquals(
            null,
            $sut->updatedAt()
        );
    }

    public function testSetUpdatedAt(): void
    {
        $sut = new User(
            new Username('foo')
        );

        $date = new \DateTimeImmutable('now');

        $sut->setUpdatedAt($date);

        $this->assertEquals(
            $date,
            $sut->updatedAt()
        );
    }

    public function testEnabled(): void
    {
        $sut = new User(
            new Username('foo')
        );

        $this->assertEquals(
            null,
            $sut->enabled()
        );
    }

    public function testSetEnabled(): void
    {
        $sut = new User(
            new Username('foo')
        );

        $sut->setEnabled(true);

        $this->assertEquals(
            true,
            $sut->enabled()
        );

        $sut->setEnabled(false);

        $this->assertEquals(
            false,
            $sut->enabled()
        );
    }

    public function testStatus(): void
    {
        $sut = new User(
            new Username('foo')
        );

        $this->assertEquals(
            'UNKNOWN',
            sprintf('%s', $sut->status())
        );
    }

    /**
     * @dataProvider statusProvider
     * @param string $statusString
     */
    public function testSetStatus(string $statusString): void
    {
        $expected = new UserStatus($statusString);

        $sut = new User(
            new Username('foo')
        );

        $sut->setStatus($expected);

        $this->assertEquals(
            $expected,
            $sut->status()
        );
    }

    public function statusProvider(): array
    {
        return [
            ['UNCONFIRMED'],
            ['CONFIRMED'],
            ['ARCHIVED'],
            ['COMPROMISED'],
            ['UNKNOWN'],
            ['RESET_REQUIRED'],
            ['FORCE_CHANGE_PASSWORD'],
        ];
    }
}
