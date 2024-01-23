<?php

declare(strict_types=1);

namespace Lexal\HttpSteppedForm\Tests\ExceptionNormalizer\Normalizers;

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
use Lexal\SteppedForm\Step\StepKey;
use PHPUnit\Framework\TestCase;

final class DefaultExceptionNormalizerTest extends TestCase
{
    private ExceptionNormalizerInterface $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new DefaultExceptionNormalizer();
    }

    public function testSupportsNormalization(): void
    {
        self::assertTrue($this->normalizer->supportsNormalization(new AlreadyStartedException('test')));
        self::assertTrue($this->normalizer->supportsNormalization(new EntityNotFoundException(new StepKey('test'))));
        self::assertTrue($this->normalizer->supportsNormalization(new FormIsNotStartedException()));
        self::assertTrue($this->normalizer->supportsNormalization(new StepNotFoundException(new StepKey('test'))));
        self::assertTrue(
            $this->normalizer->supportsNormalization(new StepNotRenderableException(new StepKey('test'))),
        );
        self::assertTrue($this->normalizer->supportsNormalization(new SteppedFormErrorsException([])));
        self::assertTrue(
            $this->normalizer->supportsNormalization(StepIsNotSubmittedException::finish(new StepKey('key'), null)),
        );
        self::assertTrue($this->normalizer->supportsNormalization(new SteppedFormException()));
    }

    public function testNormalize(): void
    {
        $exception = new EntityNotFoundException(new StepKey('test'));

        $this->expectExceptionObject($exception);

        $this->normalizer->normalize($exception, new FormSettings());
    }
}
