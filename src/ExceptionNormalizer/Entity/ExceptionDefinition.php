<?php

declare(strict_types=1);

namespace Lexal\HttpSteppedForm\ExceptionNormalizer\Entity;

use Lexal\HttpSteppedForm\Settings\FormSettingsInterface;
use Lexal\SteppedForm\Steps\Collection\Step;
use Lexal\SteppedForm\Steps\Collection\StepsCollection;

class ExceptionDefinition
{
    public function __construct(
        private FormSettingsInterface $formSettings,
        private StepsCollection $steps,
        private ?string $currentStepKey = null,
    ) {
    }

    public function getFormSettings(): FormSettingsInterface
    {
        return $this->formSettings;
    }

    /**
     * @return StepsCollection<Step>
     */
    public function getSteps(): StepsCollection
    {
        return $this->steps;
    }

    public function getCurrentStepKey(): ?string
    {
        return $this->currentStepKey;
    }
}
