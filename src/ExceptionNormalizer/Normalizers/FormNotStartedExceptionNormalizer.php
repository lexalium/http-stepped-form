<?php

declare(strict_types=1);

namespace Lexal\HttpSteppedForm\ExceptionNormalizer\Normalizers;

use Lexal\HttpSteppedForm\ExceptionNormalizer\ExceptionNormalizerInterface;
use Lexal\HttpSteppedForm\Routing\RedirectorInterface;
use Lexal\HttpSteppedForm\Settings\FormSettingsInterface;
use Lexal\SteppedForm\Exception\FormNotStartedException;
use Lexal\SteppedForm\Exception\SteppedFormException;
use Symfony\Component\HttpFoundation\Response;

final class FormNotStartedExceptionNormalizer implements ExceptionNormalizerInterface
{
    public function __construct(private readonly RedirectorInterface $redirector)
    {
    }

    public function supportsNormalization(SteppedFormException $exception): bool
    {
        return $exception instanceof FormNotStartedException;
    }

    public function normalize(SteppedFormException $exception, FormSettingsInterface $formSettings): Response
    {
        return $this->redirector->redirect($formSettings->getUrlBeforeStart(), [$exception->getMessage()]);
    }
}
