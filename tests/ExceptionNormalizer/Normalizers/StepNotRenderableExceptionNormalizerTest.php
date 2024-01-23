<?php

declare(strict_types=1);

namespace Lexal\HttpSteppedForm\Tests\ExceptionNormalizer\Normalizers;

use Lexal\HttpSteppedForm\ExceptionNormalizer\ExceptionNormalizerInterface;
use Lexal\HttpSteppedForm\ExceptionNormalizer\Normalizers\StepNotRenderableExceptionNormalizer;
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
use Symfony\Component\HttpFoundation\Response;

final class StepNotRenderableExceptionNormalizerTest extends TestCase
{
    private ExceptionNormalizerInterface $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new StepNotRenderableExceptionNormalizer();
    }

    public function testSupportsNormalization(): void
    {
        self::assertTrue(
            $this->normalizer->supportsNormalization(new StepNotRenderableException(new StepKey('test'))),
        );
        self::assertFalse($this->normalizer->supportsNormalization(new AlreadyStartedException('test')));
        self::assertFalse($this->normalizer->supportsNormalization(new EntityNotFoundException(new StepKey('test'))));
        self::assertFalse($this->normalizer->supportsNormalization(new FormIsNotStartedException()));
        self::assertFalse($this->normalizer->supportsNormalization(new StepNotFoundException(new StepKey('test'))));
        self::assertFalse($this->normalizer->supportsNormalization(new SteppedFormErrorsException([])));
        self::assertFalse(
            $this->normalizer->supportsNormalization(StepIsNotSubmittedException::finish(new StepKey('key'), null)),
        );
        self::assertFalse($this->normalizer->supportsNormalization(new SteppedFormException()));
    }

    public function testNormalize(): void
    {
        $expected = new Response(status: Response::HTTP_NOT_FOUND);

        $actual = $this->normalizer->normalize(new StepNotRenderableException(new StepKey('test')), new FormSettings());

        self::assertEquals($expected, $actual);
    }
}
