<?php

declare(strict_types=1);

namespace Lexal\HttpSteppedForm\Tests\ExceptionNormalizer\Normalizers;

use Lexal\HttpSteppedForm\ExceptionNormalizer\ExceptionNormalizerInterface;
use Lexal\HttpSteppedForm\ExceptionNormalizer\Normalizers\EntityNotFoundExceptionNormalizer;
use Lexal\HttpSteppedForm\Routing\RedirectorInterface;
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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

final class EntityNotFoundExceptionNormalizerTest extends TestCase
{
    private MockObject $redirector;
    private ExceptionNormalizerInterface $normalizer;

    protected function setUp(): void
    {
        $this->redirector = $this->createMock(RedirectorInterface::class);
        $this->normalizer = new EntityNotFoundExceptionNormalizer($this->redirector);
    }

    public function testSupportsNormalization(): void
    {
        self::assertTrue($this->normalizer->supportsNormalization(new EntityNotFoundException(new StepKey('test'))));
        self::assertFalse($this->normalizer->supportsNormalization(new AlreadyStartedException('test')));
        self::assertFalse($this->normalizer->supportsNormalization(new FormIsNotStartedException()));
        self::assertFalse($this->normalizer->supportsNormalization(new StepNotFoundException(new StepKey('test'))));
        self::assertFalse(
            $this->normalizer->supportsNormalization(new StepNotRenderableException(new StepKey('test'))),
        );
        self::assertFalse($this->normalizer->supportsNormalization(new SteppedFormErrorsException([])));
        self::assertFalse(
            $this->normalizer->supportsNormalization(StepIsNotSubmittedException::finish(new StepKey('key'), null)),
        );
        self::assertFalse($this->normalizer->supportsNormalization(new SteppedFormException()));
    }

    #[DataProvider('normalizeDataProvider')]
    public function testNormalize(?StepKey $renderable, string $expectedUrl): void
    {
        $expected = new Response();
        $exception = new EntityNotFoundException(new StepKey('test'));

        $exception->renderable = $renderable;

        $this->redirector->expects(self::once())
            ->method('redirect')
            ->with($expectedUrl, [$exception->getMessage()])
            ->willReturn($expected);

        $actual = $this->normalizer->normalize($exception, new FormSettings());

        self::assertEquals($expected, $actual);
    }

    /**
     * @return iterable<string, array<StepKey|null|string>>
     */
    public static function normalizeDataProvider(): iterable
    {
        yield 'with previously renderable step' => [new StepKey('renderable'), 'renderable'];
        yield 'without previously renderable step' => [null, 'before'];
    }
}
