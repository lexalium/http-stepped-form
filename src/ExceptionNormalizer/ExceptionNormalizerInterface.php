<?php

declare(strict_types=1);

namespace Lexal\HttpSteppedForm\ExceptionNormalizer;

use Lexal\HttpSteppedForm\ExceptionNormalizer\Entity\ExceptionDefinition;
use Lexal\SteppedForm\Exception\SteppedFormException;
use Symfony\Component\HttpFoundation\Response;

interface ExceptionNormalizerInterface
{
    /**
     * Checks if the exception can be transformed to the response.
     */
    public function supportsNormalization(SteppedFormException $exception): bool;

    /**
     * Transforms an exception to the response.
     */
    public function normalize(SteppedFormException $exception, ExceptionDefinition $definition): Response;
}
