<?php

declare(strict_types=1);

namespace Lexal\HttpSteppedForm\Tests\ExceptionNormalizer\Normalizers;

use Lexal\HttpSteppedForm\ExceptionNormalizer\ExceptionNormalizerInterface;
use Lexal\HttpSteppedForm\ExceptionNormalizer\Normalizers\AlreadyStartedExceptionNormalizer;
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

    public function testSupportsNormalization(): void
    {
        $this->assertTrue($this->normalizer->supportsNormalization(new AlreadyStartedException('test')));
        $this->assertFalse($this->normalizer->supportsNormalization(new EntityNotFoundException(new StepKey('test'))));
        $this->assertFalse($this->normalizer->supportsNormalization(new FormIsNotStartedException()));
        $this->assertFalse($this->normalizer->supportsNormalization(new StepNotFoundException(new StepKey('test'))));
        $this->assertFalse(
            $this->normalizer->supportsNormalization(new StepNotRenderableException(new StepKey('test'))),
        );
        $this->assertFalse($this->normalizer->supportsNormalization(new SteppedFormErrorsException([])));
        $this->assertFalse(
            $this->normalizer->supportsNormalization(StepIsNotSubmittedException::finish(new StepKey('key'), null)),
        );
        $this->assertFalse($this->normalizer->supportsNormalization(new SteppedFormException()));
    }


    public function testNormalizeCurrentStepExists(): void
    {
        $expected = new Response();

        $this->redirector->expects($this->once())
            ->method('redirect')
            ->with(new StepKey('test'))
            ->willReturn($expected);

        $actual = $this->normalizer->normalize(new AlreadyStartedException('test'), new FormSettings());

        $this->assertEquals($expected, $actual);
    }
}
