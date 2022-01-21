<?php

declare(strict_types=1);

namespace Lexal\HttpSteppedForm\Tests\ExceptionNormalizer\Normalizers;

use Lexal\HttpSteppedForm\ExceptionNormalizer\Entity\ExceptionDefinition;
use Lexal\HttpSteppedForm\ExceptionNormalizer\ExceptionNormalizerInterface;
use Lexal\HttpSteppedForm\ExceptionNormalizer\Normalizers\DefaultExceptionNormalizer;
use Lexal\HttpSteppedForm\Tests\FormSettings;
use Lexal\SteppedForm\Exception\AlreadyStartedException;
use Lexal\SteppedForm\Exception\EntityNotFoundException;
use Lexal\SteppedForm\Exception\FormIsNotStartedException;
use Lexal\SteppedForm\Exception\StepIsNotSubmittedException;
use Lexal\SteppedForm\Exception\StepNotFoundException;
use Lexal\SteppedForm\Exception\StepNotRenderableException;
use Lexal\SteppedForm\Exception\SteppedFormErrorsException;
use Lexal\SteppedForm\Exception\SteppedFormException;
use Lexal\SteppedForm\Steps\Collection\Step;
use Lexal\SteppedForm\Steps\Collection\StepsCollection;
use Lexal\SteppedForm\Steps\StepInterface;
use PHPUnit\Framework\TestCase;

class DefaultExceptionNormalizerTest extends TestCase
{
    private ExceptionNormalizerInterface $normalizer;

    public function testSupportsNormalization(): void
    {
        $step = new Step('test', $this->createMock(StepInterface::class));

        $this->assertTrue($this->normalizer->supportsNormalization(new AlreadyStartedException('test', null)));
        $this->assertTrue($this->normalizer->supportsNormalization(new EntityNotFoundException('test')));
        $this->assertTrue($this->normalizer->supportsNormalization(new FormIsNotStartedException()));
        $this->assertTrue($this->normalizer->supportsNormalization(new StepNotFoundException('test')));
        $this->assertTrue($this->normalizer->supportsNormalization(new StepNotRenderableException('test')));
        $this->assertTrue($this->normalizer->supportsNormalization(new SteppedFormErrorsException([])));
        $this->assertTrue($this->normalizer->supportsNormalization(new StepIsNotSubmittedException($step)));
        $this->assertTrue($this->normalizer->supportsNormalization(new SteppedFormException()));
    }

    public function testNormalize(): void
    {
        $exception = new EntityNotFoundException('test');

        $this->expectExceptionObject($exception);

        $this->normalizer->normalize($exception, new ExceptionDefinition(new FormSettings(), new StepsCollection([])));
    }

    protected function setUp(): void
    {
        $this->normalizer = new DefaultExceptionNormalizer();

        parent::setUp();
    }
}
