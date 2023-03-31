<?php

/**
 * Tests for Net\UriFactory class.
 * @package Phrity > Net > Uri
 */

declare(strict_types=1);

namespace Phrity\Net;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\{
    UriFactoryInterface,
    UriInterface
};

class UriFactoryTest extends TestCase
{
    // ---------- General tests ------------------------------------------------------------------------------------ //

    public function testEmpty(): void
    {
        $factory = new UriFactory();
        $this->assertInstanceOf(UriFactoryInterface::class, $factory);

        $uri = $factory->createUri();
        $this->assertInstanceOf(UriInterface::class, $uri);
    }

    public function tesNontEmpty(): void
    {
        $factory = new UriFactory();
        $this->assertInstanceOf(UriFactoryInterface::class, $factory);

        $uri = $factory->createUri('http://user:pass@domain.tld:123/path/page.html?q=query#fragment');
        $this->assertInstanceOf(UriInterface::class, $uri);
    }

    public function tesError(): void
    {
        $factory = new UriFactory();
        $this->assertInstanceOf(UriFactoryInterface::class, $factory);

        $this->expectException(InvalidArgumentException::class);
        $uri = $factory->createUri('urn://host:with:colon');
    }
}
