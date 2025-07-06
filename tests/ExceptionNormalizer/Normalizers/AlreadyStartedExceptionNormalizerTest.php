<?php

declare(strict_types=1);

namespace Lexal\HttpSteppedForm\Tests\ExceptionNormalizer\Normalizers;

use Lexal\HttpSteppedForm\ExceptionNormalizer\ExceptionNormalizerInterface;
use Lexal\HttpSteppedForm\ExceptionNormalizer\Normalizers\AlreadyStartedExceptionNormalizer;
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

final class AlreadyStartedExceptionNormalizerTest extends TestCase
{
    private MockObject $redirector;
    private ExceptionNormalizerInterface $normalizer;

    protected function setUp(): void
    {
        $this->redirector = $this->createMock(RedirectorInterface::class);
        $this->normalizer = new AlreadyStartedExceptionNormalizer($this->redirector);
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
        yield 'AlreadyStartedException' => [new AlreadyStartedException('test'), true];
        yield 'EntityNotFoundException' => [new EntityNotFoundException(new StepKey('test')), false];
        yield 'FormNotStartedException' => [new FormNotStartedException(), false];
        yield 'StepNotFoundException' => [new StepNotFoundException(new StepKey('test')), false];
        yield 'StepNotRenderableException' => [new StepNotRenderableException(new StepKey('test')), false];
        yield 'SteppedFormErrorsException' => [new SteppedFormErrorsException([]), false];
        yield 'StepNotSubmittedException' => [StepNotSubmittedException::finish(new StepKey('key'), null), false];
        yield 'SteppedFormException' => [new SteppedFormException(), false];
    }


    public function testNormalizeCurrentStepExists(): void
    {
        $expected = new Response();

        $this->redirector->expects(self::once())
            ->method('redirect')
            ->with(new StepKey('test'))
            ->willReturn($expected);

        $actual = $this->normalizer->normalize(new AlreadyStartedException('test'), new FormSettings());

        self::assertEquals($expected, $actual);
    }
}
