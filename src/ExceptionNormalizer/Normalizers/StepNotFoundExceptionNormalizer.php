<?php

declare(strict_types=1);

namespace Lexal\HttpSteppedForm\ExceptionNormalizer\Normalizers;

use Lexal\HttpSteppedForm\ExceptionNormalizer\ExceptionNormalizerInterface;
use Lexal\HttpSteppedForm\Settings\FormSettingsInterface;
use Lexal\SteppedForm\Exception\StepNotFoundException;
use Lexal\SteppedForm\Exception\SteppedFormException;
use Symfony\Component\HttpFoundation\Response;

final class StepNotFoundExceptionNormalizer implements ExceptionNormalizerInterface
{
    public function supportsNormalization(SteppedFormException $exception): bool
    {
        return $exception instanceof StepNotFoundException;
    }

    public function normalize(SteppedFormException $exception, FormSettingsInterface $formSettings): Response
    {
        return new Response(status: Response::HTTP_NOT_FOUND);
    }
}
