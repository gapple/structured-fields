<?php

declare(strict_types=1);

namespace gapple\StructuredFields;

use RuntimeException;

class ParseException extends RuntimeException implements StructuredFieldError
{
}
