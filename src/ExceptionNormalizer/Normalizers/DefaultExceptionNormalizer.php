<?php

declare(strict_types=1);

namespace Lexal\HttpSteppedForm\ExceptionNormalizer\Normalizers;

use Lexal\HttpSteppedForm\ExceptionNormalizer\Entity\ExceptionDefinition;
use Lexal\HttpSteppedForm\ExceptionNormalizer\ExceptionNormalizerInterface;
use Lexal\SteppedForm\Exception\SteppedFormException;
use Symfony\Component\HttpFoundation\Response;

class DefaultExceptionNormalizer implements ExceptionNormalizerInterface
{
    public function supportsNormalization(SteppedFormException $exception): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     *
     * @throws SteppedFormException
     */
    public function normalize(SteppedFormException $exception, ExceptionDefinition $definition): Response
    {
        throw $exception;
    }
}
