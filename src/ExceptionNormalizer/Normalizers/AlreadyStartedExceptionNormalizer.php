<?php

declare(strict_types=1);

namespace Lexal\HttpSteppedForm\ExceptionNormalizer\Normalizers;

use Lexal\HttpSteppedForm\ExceptionNormalizer\Entity\ExceptionDefinition;
use Lexal\HttpSteppedForm\ExceptionNormalizer\ExceptionNormalizerInterface;
use Lexal\HttpSteppedForm\Routing\RedirectorInterface;
use Lexal\SteppedForm\Exception\AlreadyStartedException;
use Lexal\SteppedForm\Exception\SteppedFormException;
use Symfony\Component\HttpFoundation\Response;

class AlreadyStartedExceptionNormalizer implements ExceptionNormalizerInterface
{
    public function __construct(private RedirectorInterface $redirector)
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
    public function normalize(SteppedFormException $exception, ExceptionDefinition $definition): Response
    {
        $step = $exception->getCurrentStep();
        $formSettings = $definition->getFormSettings();

        if ($step === null) {
            $response = $this->redirector->redirect(
                $formSettings->getUrlBeforeStart(),
                ['The form has already started. The form session canceled.'],
            );
        } else {
            $response = $this->redirector->redirect($formSettings->getStepUrl($step->getKey()));
        }

        return $response;
    }
}
