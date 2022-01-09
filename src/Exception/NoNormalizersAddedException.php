<?php

declare(strict_types=1);

namespace Lexal\HttpSteppedForm\Exception;

use Lexal\SteppedForm\Exception\SteppedFormException;

class NoNormalizersAddedException extends SteppedFormException
{
    public function __construct()
    {
        parent::__construct('You must register at least one normalizer to be able to normalize exceptions.');
    }
}
