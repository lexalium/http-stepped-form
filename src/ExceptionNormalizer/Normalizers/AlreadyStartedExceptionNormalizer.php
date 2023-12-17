<?php

declare(strict_types=1);

namespace Lexal\HttpSteppedForm\ExceptionNormalizer\Normalizers;

use Lexal\HttpSteppedForm\ExceptionNormalizer\ExceptionNormalizerInterface;
use Lexal\HttpSteppedForm\Routing\RedirectorInterface;
use Lexal\HttpSteppedForm\Settings\FormSettingsInterface;
use Lexal\SteppedForm\Exception\AlreadyStartedException;
use Lexal\SteppedForm\Exception\SteppedFormException;
use Lexal\SteppedForm\Step\StepKey;
use Symfony\Component\HttpFoundation\Response;

final class AlreadyStartedExceptionNormalizer implements ExceptionNormalizerInterface
{
    public function __construct(private readonly RedirectorInterface $redirector)
    {
    }

    public function supportsNormalization(SteppedFormException $exception): bool
    {
        return $exception instanceof AlreadyStartedException;
    }

    /**
     * @inheritDoc
     *
     * @param AlreadyStartedException $exception
     */
    public function normalize(SteppedFormException $exception, FormSettingsInterface $formSettings): Response
    {
        return $this->redirector->redirect($formSettings->getStepUrl(new StepKey($exception->currentKey)));
    }
}
