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

### Special Types
`Serializer` will accept `string` or any `Stringable` object as an Item value, 
but will throw a `SerializeException` if it contains any characters outside the
printable ASCII range.

Special Item types must use a decorating class in order to be serialized correctly:

- **Byte Sequences** (`\gapple\StructuredFields\Bytes`)  
  A string containing binary data.  

  `Serializer` and `Parser` handle base64 encoding and decoding the provided string.


- **Display Strings** (`\gapple\StructuredFields\DisplayString`)  
  A string that includes Unicode characters.

  `Serializer` and `Parser` handle percent-encoding and decoding non-ascii characters.


- **Tokens** (`\gapple\StructuredFields\Token`)  
  A short textual word with a restricted character set.
 

- **Dates** (`\gapple\StructuredFields\Date`)  
  An integer timestamp

  `Serializer` accepts any object that implements `\DateTimeInterface`.   
  `Parser` will return a `\gapple\StructuredFields\Date` object.

  
[1]: https://www.rfc-editor.org/rfc/rfc9651.html
