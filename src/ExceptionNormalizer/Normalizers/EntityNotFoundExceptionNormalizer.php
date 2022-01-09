<?php

declare(strict_types=1);

namespace Lexal\HttpSteppedForm\ExceptionNormalizer\Normalizers;

use Lexal\HttpSteppedForm\ExceptionNormalizer\Entity\ExceptionDefinition;
use Lexal\HttpSteppedForm\ExceptionNormalizer\ExceptionNormalizerInterface;
use Lexal\HttpSteppedForm\Routing\RedirectorInterface;
use Lexal\SteppedForm\Exception\EntityNotFoundException;
use Lexal\SteppedForm\Exception\SteppedFormException;
use Symfony\Component\HttpFoundation\Response;

use function sprintf;

class EntityNotFoundExceptionNormalizer implements ExceptionNormalizerInterface
{
    public function __construct(private RedirectorInterface $redirector)
    {
    }

    public function supportsNormalization(SteppedFormException $exception): bool
    {
        return $exception instanceof EntityNotFoundException;
    }

    public function normalize(SteppedFormException $exception, ExceptionDefinition $definition): Response
    {
        return $this->redirector->redirect(
            $definition->getFormSettings()->getUrlBeforeStart(),
            [sprintf('The form session canceled. %s', $exception->getMessage())],
        );
    }
}
