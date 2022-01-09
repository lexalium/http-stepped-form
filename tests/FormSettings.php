<?php

declare(strict_types=1);

namespace Lexal\HttpSteppedForm\Tests;

use Lexal\HttpSteppedForm\Settings\FormSettingsInterface;

class FormSettings implements FormSettingsInterface
{
    public function __construct(private string $urlBeforeStart = 'before', private string $urlAfterFinish = 'finish')
    {
    }

    public function getStepUrl(string $key): string
    {
        return $key;
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
