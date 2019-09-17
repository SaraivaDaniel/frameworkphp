<?php

namespace Saraiva\Framework\Validation\Exceptions;

use \Respect\Validation\Exceptions\ValidationException;

class NumberFormatException extends ValidationException
{
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => "{{name}} must be a price in BRL.",
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => '{{name}} must not be a price in BRL.',
        ],
    ];
}