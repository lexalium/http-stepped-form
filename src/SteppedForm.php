<?php

declare(strict_types=1);

namespace Lexal\HttpSteppedForm;

use Lexal\HttpSteppedForm\Exception\EntityNotFoundException as EntityNotFoundExceptionAdapter;
use Lexal\HttpSteppedForm\ExceptionNormalizer\Entity\ExceptionDefinition;
use Lexal\HttpSteppedForm\ExceptionNormalizer\ExceptionNormalizerInterface;
use Lexal\HttpSteppedForm\Renderer\RendererInterface;
use Lexal\HttpSteppedForm\Routing\RedirectorInterface;
use Lexal\HttpSteppedForm\Settings\FormSettingsInterface;
use Lexal\SteppedForm\Exception\EntityNotFoundException;
use Lexal\SteppedForm\Exception\FormIsNotStartedException;
use Lexal\SteppedForm\Exception\SteppedFormException;
use Lexal\SteppedForm\SteppedFormInterface as BaseSteppedFormInterface;
use Lexal\SteppedForm\Steps\Collection\Step;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function array_replace_recursive;

class SteppedForm implements SteppedFormInterface
{
    public function __construct(
        private BaseSteppedFormInterface $form,
        private FormSettingsInterface $formSettings,
        private RedirectorInterface $redirector,
        private RendererInterface $renderer,
        private ExceptionNormalizerInterface $exceptionNormalizer,
    ) {
    }

    public function getEntity(): mixed
    {
        return $this->form->getEntity();
    }

    public function start(mixed $entity): Response
    {
        try {
            $step = $this->form->start($entity);
        } catch (SteppedFormException $exception) {
            return $this->exceptionNormalizer->normalize(
                $exception,
                new ExceptionDefinition($this->formSettings, $this->form->getSteps()),
            );
        }

        return $this->redirectToStep($step);
    }

    /**
     * @inheritDoc
     *
     * @throws FormIsNotStartedException
     */
    public function render(string $key): Response
    {
        try {
            $definition = $this->form->render($key);
        } catch (SteppedFormException $exception) {
            return $this->handleStepException($exception, $key);
        }

        return $this->renderer->render($definition);
    }

    /**
     * @inheritDoc
     *
     * @throws FormIsNotStartedException
     */
    public function handle(string $key, Request $request): Response
    {
        $data = array_replace_recursive($request->query->all(), $request->request->all(), $request->files->all());

        try {
            $next = $this->form->handle($key, $data);
        } catch (SteppedFormException $exception) {
            return $this->handleStepException($exception, $key);
        }

        return $this->redirectToStep($next);
    }

    public function cancel(string $url): Response
    {
        try {
            $this->form->cancel();
        } catch (SteppedFormException $exception) {
            return $this->exceptionNormalizer->normalize(
                $exception,
                new ExceptionDefinition($this->formSettings, $this->form->getSteps()),
            );
        }

        return $this->redirector->redirect($url);
    }

    private function redirectToStep(?Step $step): Response
    {
        if ($step === null) {
            $url = $this->formSettings->getUrlAfterFinish();
        } else {
            $url = $this->formSettings->getStepUrl($step->getKey());
        }

        return $this->redirector->redirect($url);
    }

    /**
     * @throws FormIsNotStartedException
     */
    private function handleStepException(SteppedFormException $exception, string $key): Response
    {
        $steps = $this->form->getSteps();

        if ($exception instanceof EntityNotFoundException) {
            $exception = new EntityNotFoundExceptionAdapter($steps, $exception);

            if (!$exception->hasNotSubmittedStep()) {
                $this->form->cancel();
            }
        }

        return $this->exceptionNormalizer->normalize(
            $exception,
            new ExceptionDefinition($this->formSettings, $steps, $key),
        );
    }
}
