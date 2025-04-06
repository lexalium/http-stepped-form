<?php

declare(strict_types=1);

namespace Lexal\HttpSteppedForm\Tests\ExceptionNormalizer\Normalizers;

use Lexal\HttpSteppedForm\ExceptionNormalizer\ExceptionNormalizerInterface;
use Lexal\HttpSteppedForm\ExceptionNormalizer\Normalizers\DefaultExceptionNormalizer;
use Lexal\HttpSteppedForm\Tests\FormSettings;
use Lexal\SteppedForm\Exception\AlreadyStartedException;
use Lexal\SteppedForm\Exception\EntityNotFoundException;
use Lexal\SteppedForm\Exception\EventDispatcherException;
use Lexal\SteppedForm\Exception\FormNotStartedException;
use Lexal\SteppedForm\Exception\KeysNotFoundInStorageException;
use Lexal\SteppedForm\Exception\NoStepsAddedException;
use Lexal\SteppedForm\Exception\ReadSessionKeyException;
use Lexal\SteppedForm\Exception\StepNotFoundException;
use Lexal\SteppedForm\Exception\StepNotRenderableException;
use Lexal\SteppedForm\Exception\StepNotSubmittedException;
use Lexal\SteppedForm\Exception\SteppedFormErrorsException;
use Lexal\SteppedForm\Exception\SteppedFormException;
use Lexal\SteppedForm\Step\StepKey;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DefaultExceptionNormalizerTest extends TestCase
{
    private ExceptionNormalizerInterface $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new DefaultExceptionNormalizer();
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
        yield 'EntityNotFoundException' => [new EntityNotFoundException(new StepKey('test')), true];
        yield 'FormNotStartedException' => [new FormNotStartedException(), true];
        yield 'StepNotFoundException' => [new StepNotFoundException(new StepKey('test')), true];
        yield 'StepNotRenderableException' => [new StepNotRenderableException(new StepKey('test')), true];
        yield 'SteppedFormErrorsException' => [new SteppedFormErrorsException([]), true];
        yield 'StepNotSubmittedException' => [StepNotSubmittedException::finish(new StepKey('key'), null), true];
        yield 'SteppedFormException' => [new SteppedFormException(), true];
        yield 'ReadSessionKeyException' => [new ReadSessionKeyException(), true];
        yield 'KeysNotFoundInStorageException' => [new KeysNotFoundInStorageException(), true];
        yield 'NoStepsAddedException' => [new NoStepsAddedException(), true];
        yield 'EventDispatcherException' => [new EventDispatcherException([]), true];
    }

    public function testNormalize(): void
    {
        $exception = new EntityNotFoundException(new StepKey('test'));

        $this->expectExceptionObject($exception);

        $this->normalizer->normalize($exception, new FormSettings());
    }
}
