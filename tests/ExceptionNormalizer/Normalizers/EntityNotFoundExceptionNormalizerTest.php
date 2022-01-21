<?php

declare(strict_types=1);

namespace Lexal\HttpSteppedForm\Tests\ExceptionNormalizer\Normalizers;

use Lexal\HttpSteppedForm\Exception\EntityNotFoundException as EntityNotFoundExceptionAdapter;
use Lexal\HttpSteppedForm\ExceptionNormalizer\Entity\ExceptionDefinition;
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
use Lexal\SteppedForm\Steps\Collection\Step;
use Lexal\SteppedForm\Steps\Collection\StepsCollection;
use Lexal\SteppedForm\Steps\StepInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class EntityNotFoundExceptionNormalizerTest extends TestCase
{
    private MockObject $redirector;
    private ExceptionNormalizerInterface $normalizer;

    public function testSupportsNormalization(): void
    {
        $step = new Step('test', $this->createMock(StepInterface::class));

        $this->assertTrue($this->normalizer->supportsNormalization(new EntityNotFoundException('test')));
        $this->assertFalse($this->normalizer->supportsNormalization(new AlreadyStartedException('test', null)));
        $this->assertFalse($this->normalizer->supportsNormalization(new FormIsNotStartedException()));
        $this->assertFalse($this->normalizer->supportsNormalization(new StepNotFoundException('test')));
        $this->assertFalse($this->normalizer->supportsNormalization(new StepNotRenderableException('test')));
        $this->assertFalse($this->normalizer->supportsNormalization(new SteppedFormErrorsException([])));
        $this->assertFalse($this->normalizer->supportsNormalization(new StepIsNotSubmittedException($step)));
        $this->assertFalse($this->normalizer->supportsNormalization(new SteppedFormException()));
    }

    public function testNormalize(): void
    {
        $expected = new Response();

        $this->redirector->expects($this->once())
            ->method('redirect')
            ->with('before', ['The form session canceled. There is no data for the given [test] step.'])
            ->willReturn($expected);

        $actual = $this->normalizer->normalize(
            new EntityNotFoundException('test'),
            new ExceptionDefinition(new FormSettings(), new StepsCollection([])),
        );

        $this->assertEquals($expected, $actual);
    }

    public function testNormalizeNotSubmittedStepNotFound(): void
    {
        $expected = new Response();

        $this->redirector->expects($this->once())
            ->method('redirect')
            ->with('before', ['The form session canceled. There is no data for the given [test] step.'])
            ->willReturn($expected);

        $actual = $this->normalizer->normalize(
            new EntityNotFoundExceptionAdapter(new StepsCollection([]), new EntityNotFoundException('test')),
            new ExceptionDefinition(new FormSettings(), new StepsCollection([])),
        );

        $this->assertEquals($expected, $actual);
    }

    public function testNormalizeNotSubmittedStepFound(): void
    {
        $expected = new Response();

        $this->redirector->expects($this->once())
            ->method('redirect')
            ->with('test2', ['There is no data for the given [test2] step.'])
            ->willReturn($expected);

        $collection = new StepsCollection([
            new Step('test2', $this->createMock(StepInterface::class), isSubmitted: false),
        ]);

        $actual = $this->normalizer->normalize(
            new EntityNotFoundExceptionAdapter($collection, new EntityNotFoundException('test2')),
            new ExceptionDefinition(new FormSettings(), $collection),
        );

        $this->assertEquals($expected, $actual);
    }

    protected function setUp(): void
    {
        $this->redirector = $this->createMock(RedirectorInterface::class);
        $this->normalizer = new EntityNotFoundExceptionNormalizer($this->redirector);

        parent::setUp();
    }
}
