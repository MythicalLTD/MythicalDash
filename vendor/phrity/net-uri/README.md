[![Build Status](https://github.com/sirn-se/phrity-net-uri/actions/workflows/acceptance.yml/badge.svg)](https://github.com/sirn-se/phrity-net-uri/actions)
[![Coverage Status](https://coveralls.io/repos/github/sirn-se/phrity-net-uri/badge.svg?branch=main)](https://coveralls.io/github/sirn-se/phrity-net-uri?branch=main)

# Introduction

Implementation of the [PSR-7 UriInterface](https://www.php-fig.org/psr/psr-7/#35-psrhttpmessageuriinterface)
and [PSR-17 UriFactoryInterface](https://www.php-fig.org/psr/psr-17/#26-urifactoryinterface) interfaces.

Nothing fancy. Just working. Because I need a URI implementation **not** hadwired to HTTP messaging.
And some extras. Allows all valid schemes.

## Installation

Install with [Composer](https://getcomposer.org/);
```
composer require phrity/net-uri
```

## Modifiers

Out of the box, it will behave as specified by PSR standards.
To change behaviour, there are some modifiers available.
These can be added as last argument in all `get` and `with` methods, plus the `toString` method.

`REQUIRE_PORT`

By PSR standard, if port is default for scheme it will be hidden.
This options will attempt to always show the port.
If set, it will be shown even if default. If not set, it will use default port (if available).

`ABSOLUTE_PATH`

Will cause paths to use absolute form, i.e. starting with `/`.

`NORMALIZE_PATH`

Will attempt to normalize paths, e.g. `./a/./path/../to//something` will transform to `a/to/something`.

`IDNA`

Will IDNA-convert host using non-ASCII characters.


### Examples

```php
$uri = new Uri('http://example.com');
$uri->getPath(Uri::REQUIRE_PORT); // => 80
$uri->toString(Uri::REQUIRE_PORTH); // => 'http://example.com:80'

$uri = new Uri('a/./path/../to//something');
$uri->getPath(Uri::ABSOLUTE_PATH | Uri::NORMALIZE_PATH); // => '/a/to/something'
$uri->toString(Uri::ABSOLUTE_PATH | Uri::NORMALIZE_PATH); // => '/a/to/something'

$clone = $uri->withPath('path/./somewhere/else/..', Uri::ABSOLUTE_PATH | Uri::NORMALIZE_PATH);
$clone->getPath(); // => '/path/somewhere'

$uri = new Uri('https://ηßöø必Дあ.com');
$uri->getHost(Uri::IDNA); // => 'xn--zca0cg32z7rau82strvd.com'
```


## Classes

There are two available classes, `Uri` and `UriFactory`.

### The Uri class

```php
class Phrity\Net\Uri implements Psr\Http\Message\UriInterface
{
    // Constructor

    public function __construct(string $uri_string = '');

    // PSR-7 getters

    public function getScheme(int $flags = 0): string;
    public function getAuthority(int $flags = 0): string;
    public function getUserInfo(int $flags = 0): string;
    public function getHost(int $flags = 0): string;
    public function getPort(int $flags = 0): ?int;
    public function getPath(int $flags = 0): string;
    public function getQuery(int $flags = 0): string;
    public function getFragment(int $flags = 0): string;

    // PSR-7 setters

    public function withScheme($scheme, int $flags = 0): UriInterface;
    public function withUserInfo($user, $password = null, int $flags = 0): UriInterface;
    public function withHost($host, int $flags = 0): UriInterface;
    public function withPort($port, int $flags = 0): UriInterface;
    public function withPath($path, int $flags = 0): UriInterface;
    public function withQuery($query, int $flags = 0): UriInterface;
    public function withFragment($fragment, int $flags = 0): UriInterface;

    // PSR-7 string representation

    public function __toString(): string;

    // Additional methods

    public function toString(int $flags = 0)): string;
}
```

### The UriFactory class

```php
class Phrity\Net\UriFactory implements Psr\Http\Message\UriFactoryInterface
{
    // Constructor

    public function __construct();

    // PSR-17 factory

    public function createUri(string $uri = ''): UriInterface;
}
```


## Versions

| Version | PHP | |
| --- | --- | --- |
| `1.2` | `^7.4\|^8.0` | IDNA modifier |
| `1.1` | `^7.4\|^8.0` | Require port, Absolute path, Normalize path modifiers |
| `1.0` | `^7.4\|^8.0` | Initial version |
