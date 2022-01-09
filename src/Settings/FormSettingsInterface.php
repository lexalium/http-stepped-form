<?php

declare(strict_types=1);

namespace Lexal\HttpSteppedForm\Settings;

interface FormSettingsInterface
{
    /**
     * Returns a URL to the form step by its key.
     */
    public function getStepUrl(string $key): string;

    /**
     * Returns a URL to redirect to when the form was finishing.
     */
    public function getUrlAfterFinish(): string;

    /**
     * Returns a URL to redirect to if step entity is not found.
     */
    public function getUrlBeforeStart(): string;
}
