<?php

declare(strict_types=1);

namespace Lexal\HttpSteppedForm\Exception;

use Lexal\SteppedForm\Exception\SteppedFormException;

use function get_class;
use function sprintf;

class NormalizerNotFoundException extends SteppedFormException
{
    public function __construct(SteppedFormException $exception)
    {
        parent::__construct(
            sprintf('Could not normalize exception [%s], no supporting normalizer found.', get_class($exception)),
        );
    }
}
