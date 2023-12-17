<?php

declare(strict_types=1);

namespace Lexal\HttpSteppedForm\Tests;

use Lexal\HttpSteppedForm\Settings\FormSettingsInterface;
use Lexal\SteppedForm\Step\StepKey;

final class FormSettings implements FormSettingsInterface
{
    public function __construct(
        private readonly string $urlBeforeStart = 'before',
        private readonly string $urlAfterFinish = 'finish',
    ) {
    }

    public function getStepUrl(StepKey $key): string
    {
        return (string)$key;
    }

    public function getUrlAfterFinish(): string
    {
        return $this->urlAfterFinish;
    }

    public function getUrlBeforeStart(): string
    {
        return $this->urlBeforeStart;
    }
}
