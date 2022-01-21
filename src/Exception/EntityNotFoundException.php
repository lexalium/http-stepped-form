<?php

declare(strict_types=1);

namespace Lexal\HttpSteppedForm\Exception;

use Lexal\SteppedForm\Exception\EntityNotFoundException as BaseEntityNotFoundException;
use Lexal\SteppedForm\Steps\Collection\Step;
use Lexal\SteppedForm\Steps\Collection\StepsCollection;

class EntityNotFoundException extends BaseEntityNotFoundException
{
    private ?Step $notSubmittedStep = null;

    /**
     * @param StepsCollection<Step> $collection
     */
    public function __construct(private StepsCollection $collection, BaseEntityNotFoundException $exception)
    {
        parent::__construct($exception->getKey());

        $this->setNotSubmittedStep();
    }

    public function hasNotSubmittedStep(): bool
    {
        return $this->notSubmittedStep !== null;
    }

    public function getNotSubmittedStep(): ?Step
    {
        return $this->notSubmittedStep;
    }

    private function setNotSubmittedStep(): void
    {
        /** @var Step $step */
        foreach ($this->collection as $step) {
            if (!$step->isSubmitted()) {
                $this->notSubmittedStep = $step;
                return;
            }
        }
    }
}
