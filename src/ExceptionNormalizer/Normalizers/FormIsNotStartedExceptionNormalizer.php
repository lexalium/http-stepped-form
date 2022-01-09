<?php

declare(strict_types=1);

namespace Lexal\HttpSteppedForm\ExceptionNormalizer\Normalizers;

use Lexal\HttpSteppedForm\ExceptionNormalizer\Entity\ExceptionDefinition;
use Lexal\HttpSteppedForm\ExceptionNormalizer\ExceptionNormalizerInterface;
use Lexal\HttpSteppedForm\Routing\RedirectorInterface;
use Lexal\SteppedForm\Exception\FormIsNotStartedException;
use Lexal\SteppedForm\Exception\SteppedFormException;
use Symfony\Component\HttpFoundation\Response;

class FormIsNotStartedExceptionNormalizer implements ExceptionNormalizerInterface
{
    public function __construct(private RedirectorInterface $redirector)
    {
    }

    public function supportsNormalization(SteppedFormException $exception): bool
    {
        return $exception instanceof FormIsNotStartedException;
    }

    public function normalize(SteppedFormException $exception, ExceptionDefinition $definition): Response
    {
        return $this->redirector->redirect(
            $definition->getFormSettings()->getUrlBeforeStart(),
            [$exception->getMessage()],
        );
    }
}
