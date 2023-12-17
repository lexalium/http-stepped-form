<?php

declare(strict_types=1);

namespace Lexal\HttpSteppedForm\ExceptionNormalizer\Normalizers;

use Lexal\HttpSteppedForm\ExceptionNormalizer\ExceptionNormalizerInterface;
use Lexal\HttpSteppedForm\Routing\RedirectorInterface;
use Lexal\HttpSteppedForm\Settings\FormSettingsInterface;
use Lexal\SteppedForm\Exception\FormIsNotStartedException;
use Lexal\SteppedForm\Exception\SteppedFormException;
use Symfony\Component\HttpFoundation\Response;

final class FormIsNotStartedExceptionNormalizer implements ExceptionNormalizerInterface
{
    public function __construct(private readonly RedirectorInterface $redirector)
    {
    }

    public function supportsNormalization(SteppedFormException $exception): bool
    {
        return $exception instanceof FormIsNotStartedException;
    }

    public function normalize(SteppedFormException $exception, FormSettingsInterface $formSettings): Response
    {
        return $this->redirector->redirect($formSettings->getUrlBeforeStart(), [$exception->getMessage()]);
    }
}
