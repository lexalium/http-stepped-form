<?php

declare(strict_types=1);

namespace Lexal\HttpSteppedForm;

use Lexal\SteppedForm\Exception\FormIsNotStartedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface SteppedFormInterface
{
    /**
     * Returns a form data.
     *
     * @throws FormIsNotStartedException
     */
    public function getEntity(): mixed;

    /**
     * Starts a new form session and redirects to the first rendered step.
     */
    public function start(mixed $entity): Response;

    /**
     * Returns a response with rendered step.
     */
    public function render(string $key): Response;

    /**
     * Handles a form step and redirects to the next step or the url after finish when there is no next step.
     */
    public function handle(string $key, Request $request): Response;

    /**
     * Cancels current form session and redirects to given URL.
     */
    public function cancel(string $url): Response;
}
