Structured Headers parser for PHP
=======================================

Parser and serializer for the [Structured Headers for HTTP][1] specification.

[![Build Status](https://github.com/gapple/structured-headers/workflows/PHP%20Composer/badge.svg)](https://github.com/gapple/structured-headers/actions)
[![codecov](https://codecov.io/gh/gapple/structured-headers/branch/master/graph/badge.svg)](https://codecov.io/gh/gapple/structured-headers)


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
print_r(\gapple\StructuredHeaders\Parser::parseList("1, 42;towel;panic=?0"));

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
//                     [towel] => 1
//                     [panic] =>
//                 )
//         )
// )
```

### Parsing a Dictionary

The `Parser::parseDictionary()` method returns a `\stdClass` object with `[value, parameters]` tuples.

```php
print_r(\gapple\StructuredHeaders\Parser::parseDictionary("towel, panic=?0"));

// stdClass Object
// (
//     [towel] => Array
//         (
//             [0] => 1
//             [1] => stdClass Object
//                 (
//                 )
//         )
//     [panic] => Array
//         (
//             [0] =>
//             [1] => stdClass Object
//                 (
//                 )
//         )
// )

```

### Serializing an Item

The `Serializer::serializeItem()` method.

```php
print_r(\gapple\StructuredHeaders\Serializer::serializeItem(true));
// ?1

print_r(\gapple\StructuredHeaders\Serializer::serializeItem(42));
// 42

print_r(\gapple\StructuredHeaders\Serializer::serializeItem("42"));
// "42"

print_r(\gapple\StructuredHeaders\Serializer::serializeItem(new \gapple\StructuredHeaders\Bytes('🙂')));
// :8J+Zgg==:
```

### Serializing a List

The `Serializer::serializeList()` method.

```php
print_r(\gapple\StructuredHeaders\Serializer::serializeList([
    [1, (object) []],
    [42, (object) ['towel' => true, 'panic' => false]],
]));

// 1, 42;towel;panic=?0
```

### Serializing a Dictionary

The `Serializer::serializeDictionary()` method.

```php
print_r(\gapple\StructuredHeaders\Serializer::serializeDictionary((object) [
    'towel' => [true, (object) []],
    'panic' => [false, (object) []],
]));

// towel, panic=?0

```

[1]: https://httpwg.org/http-extensions/draft-ietf-httpbis-header-structure.html
