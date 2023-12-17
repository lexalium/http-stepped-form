<?php

declare(strict_types=1);

namespace Lexal\HttpSteppedForm\ExceptionNormalizer\Normalizers;

use Lexal\HttpSteppedForm\ExceptionNormalizer\ExceptionNormalizerInterface;
use Lexal\HttpSteppedForm\Routing\RedirectorInterface;
use Lexal\HttpSteppedForm\Settings\FormSettingsInterface;
use Lexal\SteppedForm\Exception\StepIsNotSubmittedException;
use Lexal\SteppedForm\Exception\SteppedFormException;
use Symfony\Component\HttpFoundation\Response;

final class StepIsNotSubmittedExceptionNormalizer implements ExceptionNormalizerInterface
{
    public function __construct(private readonly RedirectorInterface $redirector)
    {
    }

    public function supportsNormalization(SteppedFormException $exception): bool
    {
        return $exception instanceof StepIsNotSubmittedException;
    }

    /**
     * @param StepIsNotSubmittedException $exception
     */
    public function normalize(SteppedFormException $exception, FormSettingsInterface $formSettings): Response
    {
        if ($exception->renderable === null) {
            $url = $formSettings->getUrlBeforeStart();
        } else {
            $url = $formSettings->getStepUrl($exception->renderable);
        }

        return $this->redirector->redirect($url, [$exception->getMessage()]);
    }
}
