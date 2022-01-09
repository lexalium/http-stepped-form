<?php

declare(strict_types=1);

namespace Lexal\HttpSteppedForm\ExceptionNormalizer\Normalizers;

use Lexal\HttpSteppedForm\ExceptionNormalizer\Entity\ExceptionDefinition;
use Lexal\HttpSteppedForm\ExceptionNormalizer\ExceptionNormalizerInterface;
use Lexal\HttpSteppedForm\Routing\RedirectorInterface;
use Lexal\SteppedForm\Exception\StepNotFoundException;
use Lexal\SteppedForm\Exception\SteppedFormErrorsException;
use Lexal\SteppedForm\Exception\SteppedFormException;
use Lexal\SteppedForm\Steps\Collection\Step;
use Lexal\SteppedForm\Steps\Collection\StepsCollection;
use Lexal\SteppedForm\Steps\RenderStepInterface;
use Symfony\Component\HttpFoundation\Response;

class SteppedFormErrorsExceptionNormalizer implements ExceptionNormalizerInterface
{
    public function __construct(private RedirectorInterface $redirector)
    {
    }

    public function supportsNormalization(SteppedFormException $exception): bool
    {
        return $exception instanceof SteppedFormErrorsException;
    }

    /**
     * @inheritDoc
     *
     * @param SteppedFormErrorsException $exception
     *
     * @throws StepNotFoundException
     */
    public function normalize(SteppedFormException $exception, ExceptionDefinition $definition): Response
    {
        $key = $definition->getCurrentStepKey();
        $formSettings = $definition->getFormSettings();

        if ($key === null) {
            $url = $formSettings->getUrlBeforeStart();
        } else {
            $step = $definition->getSteps()->get($key);

            $renderable = $this->getLastRenderableStepByCurrent($step, $definition->getSteps());

            $url = $renderable !== null ? $formSettings->getStepUrl($renderable->getKey())
                : $formSettings->getUrlBeforeStart();
        }

        return $this->redirector->redirect($url, $exception->getErrors());
    }

    /**
     * @param StepsCollection<Step> $steps
     *
     * @throws StepNotFoundException
     */
    private function getLastRenderableStepByCurrent(Step $step, StepsCollection $steps): ?Step
    {
        if ($step->getStep() instanceof RenderStepInterface) {
            return $step;
        }

        $previous = $steps->previous($step->getKey());

        return $previous !== null ? $this->getLastRenderableStepByCurrent($previous, $steps) : null;
    }
}
