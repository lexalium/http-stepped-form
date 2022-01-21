<?php

declare(strict_types=1);

namespace Lexal\HttpSteppedForm\ExceptionNormalizer\Normalizers;

use Lexal\HttpSteppedForm\ExceptionNormalizer\Entity\ExceptionDefinition;
use Lexal\HttpSteppedForm\ExceptionNormalizer\ExceptionNormalizerInterface;
use Lexal\HttpSteppedForm\Routing\RedirectorInterface;
use Lexal\SteppedForm\Exception\StepIsNotSubmittedException;
use Lexal\SteppedForm\Exception\SteppedFormException;
use Symfony\Component\HttpFoundation\Response;

class StepIsNotSubmittedExceptionNormalizer implements ExceptionNormalizerInterface
{
    public function __construct(private RedirectorInterface $redirector)
    {
    }

    public function supportsNormalization(SteppedFormException $exception): bool
    {
        return $exception instanceof StepIsNotSubmittedException;
    }

    /**
     * @param StepIsNotSubmittedException $exception
     */
    public function normalize(SteppedFormException $exception, ExceptionDefinition $definition): Response
    {
        $url = $definition->getFormSettings()->getStepUrl($exception->getStep()->getKey());

        return $this->redirector->redirect($url, [$exception->getMessage()]);
    }
}
