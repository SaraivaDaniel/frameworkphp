<?php

namespace Saraiva\Framework\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;
use Saraiva\Framework\Helper\InputHelper;

class NumberFormat extends AbstractRule
{
    public function validate($input)
    {
        $result = InputHelper::validate_numberformat($input);
        return ($result !== FALSE) ? TRUE : FALSE;
    }
}