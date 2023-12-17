<?php

declare(strict_types=1);

namespace Lexal\HttpSteppedForm\Tests\ExceptionNormalizer;

use InvalidArgumentException;
use Lexal\HttpSteppedForm\Exception\EmptyNoNormalizersException;
use Lexal\HttpSteppedForm\Exception\NormalizerNotFoundException;
use Lexal\HttpSteppedForm\ExceptionNormalizer\ExceptionNormalizer;
use Lexal\HttpSteppedForm\ExceptionNormalizer\ExceptionNormalizerInterface;
use Lexal\HttpSteppedForm\Tests\FormSettings;
use Lexal\SteppedForm\Exception\SteppedFormException;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\HttpFoundation\Response;

use function sprintf;

final class ExceptionNormalizerTest extends TestCase
{
    private ExceptionNormalizerInterface&Stub $normalizer;
    private ExceptionNormalizerInterface $exceptionNormalizer;

    protected function setUp(): void
    {
        $this->normalizer = $this->createMock(ExceptionNormalizerInterface::class);
        $this->exceptionNormalizer = new ExceptionNormalizer([$this->normalizer]);
    }

    public function testNoNormalizersAddedException(): void
    {
        $this->expectExceptionObject(new EmptyNoNormalizersException());

        new ExceptionNormalizer([]);
    }

    public function testInvalidNormalizerType(): void
    {
        $this->expectExceptionObject(new InvalidArgumentException(
            sprintf(
                'The class [%s] must implement the [%s] interface.',
                stdClass::class,
                ExceptionNormalizerInterface::class,
            ),
        ));

        /** @phpstan-ignore-next-line */
        new ExceptionNormalizer([new stdClass()]);
    }

    public function testSupportsNormalizationExists(): void
    {
        $this->normalizer->method('supportsNormalization')
            ->willReturn(true);

        $this->assertTrue($this->exceptionNormalizer->supportsNormalization(new SteppedFormException()));
    }

    public function testSupportsNormalizationNotExists(): void
    {
        $this->normalizer->method('supportsNormalization')
            ->willReturn(false);

        $this->assertFalse($this->exceptionNormalizer->supportsNormalization(new SteppedFormException()));
    }

    public function testNormalizeExists(): void
    {
        $this->normalizer->method('normalize')
            ->willReturn(new Response());

        $this->normalizer->method('supportsNormalization')
            ->willReturn(true);

        $actual = $this->exceptionNormalizer->normalize(new SteppedFormException(), new FormSettings());

        $this->assertEquals(new Response(), $actual);
    }

    public function testNormalizeNotExists(): void
    {
        $this->expectExceptionObject(new NormalizerNotFoundException(new SteppedFormException()));

        $this->normalizer->method('supportsNormalization')
            ->willReturn(false);

        $this->exceptionNormalizer->normalize(new SteppedFormException(), new FormSettings());
    }
}
