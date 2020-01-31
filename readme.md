Structured Headers parser for PHP
=======================================

Parser and serializer for the [Structured Headers for HTTP][1] specification.

Installation
------------

Using composer:

```
composer require gapple/structured-headers
```

API
---

### Parsing an Item

The `Parser::parseItem()` method returns a `[value, parameters]` tuple.

```php
print_r(\gapple\StructuredHeaders\Parser::parseItem("42"));

// Array
// (
//     [0] => 42
//     [1] => stdClass Object
//         (
//         )
// )
```

### Parsing a List

The `Parser::parseList()` method returns an array of `[value, parameters]` tuples.

```php
print_r(\gapple\StructuredHeaders\Parser::parseList("1, 42"));

// Array
// (
//     [0] => Array
//         (
//             [0] => 1
//             [1] => stdClass Object
//                 (
//                 )
//         )
//     [1] => Array
//         (
//             [0] => 42
//             [1] => stdClass Object
//                 (
//                 )
//         )
// )
```

[1]: https://httpwg.org/http-extensions/draft-ietf-httpbis-header-structure.html
