Structured Field Values Parser for PHP
=======================================

Parser and serializer for the [Structured Field Values for HTTP][1] specification.

[![Build Status](https://github.com/gapple/structured-fields/workflows/PHP%20Composer/badge.svg)](https://github.com/gapple/structured-fields/actions)
[![codecov](https://codecov.io/gh/gapple/structured-fields/branch/master/graph/badge.svg)](https://codecov.io/gh/gapple/structured-fields)


Installation
------------

Using composer:

```
composer require gapple/structured-fields
```

API
---

### Parsing an Item

The `Parser::parseItem()` method returns a `[value, parameters]` tuple.

```php
print_r(\gapple\StructuredFields\Parser::parseItem("42"));

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
print_r(\gapple\StructuredFields\Parser::parseList("1, 42;towel;panic=?0"));

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
print_r(\gapple\StructuredFields\Parser::parseDictionary("towel, panic=?0"));

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
print_r(\gapple\StructuredFields\Serializer::serializeItem(true));
// ?1

print_r(\gapple\StructuredFields\Serializer::serializeItem(42));
// 42

print_r(\gapple\StructuredFields\Serializer::serializeItem("42"));
// "42"

print_r(\gapple\StructuredHeStructuredFieldsaders\Serializer::serializeItem(new \gapple\StructuredFields\Bytes('🙂')));
// :8J+Zgg==:
```

### Serializing a List

The `Serializer::serializeList()` method.

```php
print_r(\gapple\StructuredFields\Serializer::serializeList([
    [1, (object) []],
    [42, (object) ['towel' => true, 'panic' => false]],
]));

// 1, 42;towel;panic=?0
```

### Serializing a Dictionary

The `Serializer::serializeDictionary()` method.

```php
print_r(\gapple\StructuredFields\Serializer::serializeDictionary((object) [
    'towel' => [true, (object) []],
    'panic' => [false, (object) []],
]));

// towel, panic=?0

```

[1]: https://httpwg.org/http-extensions/draft-ietf-httpbis-header-structure.html
