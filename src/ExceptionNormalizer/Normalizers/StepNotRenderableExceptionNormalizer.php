<?php

declare(strict_types=1);

namespace Lexal\HttpSteppedForm\ExceptionNormalizer\Normalizers;

use Lexal\HttpSteppedForm\ExceptionNormalizer\ExceptionNormalizerInterface;
use Lexal\HttpSteppedForm\Settings\FormSettingsInterface;
use Lexal\SteppedForm\Exception\StepNotRenderableException;
use Lexal\SteppedForm\Exception\SteppedFormException;
use Symfony\Component\HttpFoundation\Response;

final class StepNotRenderableExceptionNormalizer implements ExceptionNormalizerInterface
{
    public function supportsNormalization(SteppedFormException $exception): bool
    {
        return $exception instanceof StepNotRenderableException;
    }

    public function normalize(SteppedFormException $exception, FormSettingsInterface $formSettings): Response
    {
        return new Response(status: Response::HTTP_NOT_FOUND);
    }
}
