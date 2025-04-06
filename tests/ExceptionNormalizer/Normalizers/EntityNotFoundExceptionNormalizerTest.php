<?php

declare(strict_types=1);

namespace Lexal\HttpSteppedForm\Tests\ExceptionNormalizer\Normalizers;

use Lexal\HttpSteppedForm\ExceptionNormalizer\ExceptionNormalizerInterface;
use Lexal\HttpSteppedForm\ExceptionNormalizer\Normalizers\EntityNotFoundExceptionNormalizer;
use Lexal\HttpSteppedForm\Routing\RedirectorInterface;
use Lexal\HttpSteppedForm\Tests\FormSettings;
use Lexal\SteppedForm\Exception\AlreadyStartedException;
use Lexal\SteppedForm\Exception\EntityNotFoundException;
use Lexal\SteppedForm\Exception\FormNotStartedException;
use Lexal\SteppedForm\Exception\StepNotFoundException;
use Lexal\SteppedForm\Exception\StepNotRenderableException;
use Lexal\SteppedForm\Exception\StepNotSubmittedException;
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

    #[DataProvider('supportsNormalizationDataProvider')]
    public function testSupportsNormalization(SteppedFormException $exception, bool $expected): void
    {
        self::assertEquals($expected, $this->normalizer->supportsNormalization($exception));
    }

    /**
     * @return iterable<string, array{ 0: SteppedFormException, 1: boolean }>
     */
    public static function supportsNormalizationDataProvider(): iterable
    {
        yield 'EntityNotFoundException' => [new EntityNotFoundException(new StepKey('test')), true];
        yield 'AlreadyStartedException' => [new AlreadyStartedException('test'), false];
        yield 'FormNotStartedException' => [new FormNotStartedException(), false];
        yield 'StepNotFoundException' => [new StepNotFoundException(new StepKey('test')), false];
        yield 'StepNotRenderableException' => [new StepNotRenderableException(new StepKey('test')), false];
        yield 'SteppedFormErrorsException' => [new SteppedFormErrorsException([]), false];
        yield 'StepNotSubmittedException' => [StepNotSubmittedException::finish(new StepKey('key'), null), false];
        yield 'SteppedFormException' => [new SteppedFormException(), false];
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
