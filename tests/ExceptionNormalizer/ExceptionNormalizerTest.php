<?php

declare(strict_types=1);

namespace Lexal\HttpSteppedForm\Tests\ExceptionNormalizer;

use InvalidArgumentException;
use Lexal\HttpSteppedForm\Exception\EmptyNormalizersException;
use Lexal\HttpSteppedForm\Exception\NormalizerNotFoundException;
use Lexal\HttpSteppedForm\ExceptionNormalizer\ExceptionNormalizer;
use Lexal\HttpSteppedForm\ExceptionNormalizer\ExceptionNormalizerInterface;
use Lexal\HttpSteppedForm\Tests\FormSettings;
use Lexal\SteppedForm\Exception\SteppedFormException;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\HttpFoundation\Response;

use function sprintf;

final class ExceptionNormalizerTest extends TestCase
{
    public function testNoNormalizersAddedException(): void
    {
        $this->expectExceptionObject(new EmptyNormalizersException());
        $this->expectExceptionMessage('You must register at least one normalizer to be able to normalize exceptions.');

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
        $normalizer = $this->createMock(ExceptionNormalizerInterface::class);

        $exceptionNormalizer = new ExceptionNormalizer([$normalizer]);

        $normalizer->method('supportsNormalization')
            ->willReturn(true);

        self::assertTrue($exceptionNormalizer->supportsNormalization(new SteppedFormException()));
    }

    public function testSupportsNormalizationNotExists(): void
    {
        $normalizer = $this->createMock(ExceptionNormalizerInterface::class);

        $exceptionNormalizer = new ExceptionNormalizer([$normalizer]);

        $normalizer->method('supportsNormalization')
            ->willReturn(false);

        self::assertFalse($exceptionNormalizer->supportsNormalization(new SteppedFormException()));
    }

    public function testNormalizeExists(): void
    {
        $normalizer1 = $this->createMock(ExceptionNormalizerInterface::class);
        $normalizer2 = $this->createMock(ExceptionNormalizerInterface::class);

        $exceptionNormalizer = new ExceptionNormalizer([$normalizer1, $normalizer2]);

        $normalizer1->method('supportsNormalization')
            ->willReturn(true);

        $normalizer1->method('normalize')
            ->willReturn(new Response('correct content'));

        $normalizer2->method('supportsNormalization')
            ->willReturn(true);

        $normalizer2->method('normalize')
            ->willReturn(new Response('incorrect content'));

        $actual = $exceptionNormalizer->normalize(new SteppedFormException(), new FormSettings());

        self::assertEquals(new Response('correct content'), $actual);
    }

    public function testNormalizeNotExists(): void
    {
        $this->expectExceptionObject(new NormalizerNotFoundException(new SteppedFormException()));
        $this->expectExceptionMessage(
            sprintf('Could not normalize exception [%s], no supporting normalizer found.', SteppedFormException::class),
        );

        $normalizer = $this->createMock(ExceptionNormalizerInterface::class);

        $exceptionNormalizer = new ExceptionNormalizer([$normalizer]);

        $normalizer->method('supportsNormalization')
            ->willReturn(false);

        $exceptionNormalizer->normalize(new SteppedFormException(), new FormSettings());
    }
}
