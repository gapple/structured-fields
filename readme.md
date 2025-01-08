Structured Field Values for PHP
=======================================

Parser and serializer for the [Structured Field Values for HTTP][1] specification.

[![Build Status](https://github.com/gapple/structured-fields/actions/workflows/php.yml/badge.svg)](https://github.com/gapple/structured-fields/actions/workflows/php.yml)
[![Code Coverage](https://codecov.io/gh/gapple/structured-fields/branch/develop/graph/badge.svg)](https://codecov.io/gh/gapple/structured-fields)
[![Latest Release](https://img.shields.io/github/v/release/gapple/structured-fields)](https://github.com/gapple/structured-fields/releases)
[![Packagist Downloads](https://img.shields.io/packagist/dm/gapple/structured-fields)](https://packagist.org/packages/gapple/structured-fields)


Installation
------------

Using composer:

```
composer require gapple/structured-fields
```

API
---

The `Parser` class provides static methods to convert a header string to structured data.  
If the string cannot be parsed, a `ParseException` is thrown.

```
Parser::parseItem(string): Item;
Parser::parseList(string): OuterList;
Parser::parseDictionary(string): Dictionary;
```

The `Serializer` class provides static methods to convert structured data to a header string.  
If the input cannot be serialized, a `SerializeException` is thrown.

```
Serializer::serializeItem(mixed, ?object): string;
Serializer::serializeList(iterable): string;
Serializer::serializeDictionary(object): string;
```

[1]: https://www.rfc-editor.org/rfc/rfc9651.html
