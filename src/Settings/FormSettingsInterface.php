<?php

declare(strict_types=1);

namespace Lexal\HttpSteppedForm\Settings;

use Lexal\SteppedForm\Step\StepKey;

interface FormSettingsInterface
{
    /**
     * Returns a URL to the form step by its key.
     */
    public function getStepUrl(StepKey $key): string;

    /**
     * Returns a URL to redirect to when the form is finished.
     */
    public function getUrlAfterFinish(): string;

    /**
     * Returns a URL to redirect to when there is no renderable step.
     */
    public function getUrlBeforeStart(): string;
}
