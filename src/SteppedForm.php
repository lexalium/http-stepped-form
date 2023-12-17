<?php

declare(strict_types=1);

namespace Lexal\HttpSteppedForm;

use Lexal\HttpSteppedForm\ExceptionNormalizer\ExceptionNormalizerInterface;
use Lexal\HttpSteppedForm\Renderer\RendererInterface;
use Lexal\HttpSteppedForm\Routing\RedirectorInterface;
use Lexal\HttpSteppedForm\Settings\FormSettingsInterface;
use Lexal\SteppedForm\Exception\SteppedFormException;
use Lexal\SteppedForm\Step\StepKey;
use Lexal\SteppedForm\SteppedFormInterface as BaseSteppedFormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function array_replace_recursive;

final class SteppedForm implements SteppedFormInterface
{
    public function __construct(
        private readonly BaseSteppedFormInterface $form,
        private readonly FormSettingsInterface $formSettings,
        private readonly RedirectorInterface $redirector,
        private readonly RendererInterface $renderer,
        private readonly ExceptionNormalizerInterface $exceptionNormalizer,
    ) {
    }

    public function getEntity(): mixed
    {
        return $this->form->getEntity();
    }

    public function start(mixed $entity): Response
    {
        try {
            $key = $this->form->start($entity);
        } catch (SteppedFormException $exception) {
            return $this->handleFormException($exception);
        }

        return $this->redirectToStep($key);
    }

    public function render(string $key): Response
    {
        try {
            $definition = $this->form->render(new StepKey($key));
        } catch (SteppedFormException $exception) {
            return $this->handleFormException($exception);
        }

        return $this->renderer->render($definition);
    }

    public function handle(string $key, Request $request): Response
    {
        $data = array_replace_recursive($request->query->all(), $request->request->all(), $request->files->all());

        try {
            $next = $this->form->handle(new StepKey($key), $data);
        } catch (SteppedFormException $exception) {
            return $this->handleFormException($exception);
        }

        return $this->redirectToStep($next);
    }

    public function cancel(string $url): Response
    {
        try {
            $this->form->cancel();
        } catch (SteppedFormException $exception) {
            return $this->handleFormException($exception);
        }

        return $this->redirector->redirect($url);
    }

    private function redirectToStep(?StepKey $key): Response
    {
        if ($key === null) {
            $url = $this->formSettings->getUrlAfterFinish();
        } else {
            $url = $this->formSettings->getStepUrl($key);
        }

        return $this->redirector->redirect($url);
    }

    private function handleFormException(SteppedFormException $exception): Response
    {
        return $this->exceptionNormalizer->normalize($exception, $this->formSettings);
    }
}
