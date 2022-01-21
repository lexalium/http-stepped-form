<?php

declare(strict_types=1);

namespace Lexal\HttpSteppedForm\Tests\ExceptionNormalizer\Normalizers;

use Lexal\HttpSteppedForm\ExceptionNormalizer\Entity\ExceptionDefinition;
use Lexal\HttpSteppedForm\ExceptionNormalizer\ExceptionNormalizerInterface;
use Lexal\HttpSteppedForm\ExceptionNormalizer\Normalizers\StepNotFoundExceptionNormalizer;
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
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class StepNotFoundExceptionNormalizerTest extends TestCase
{
    private ExceptionNormalizerInterface $normalizer;

    public function testSupportsNormalization(): void
    {
        $step = new Step('test', $this->createMock(StepInterface::class));

        $this->assertTrue($this->normalizer->supportsNormalization(new StepNotFoundException('test')));
        $this->assertFalse($this->normalizer->supportsNormalization(new AlreadyStartedException('test', null)));
        $this->assertFalse($this->normalizer->supportsNormalization(new EntityNotFoundException('test')));
        $this->assertFalse($this->normalizer->supportsNormalization(new FormIsNotStartedException()));
        $this->assertFalse($this->normalizer->supportsNormalization(new StepNotRenderableException('test')));
        $this->assertFalse($this->normalizer->supportsNormalization(new SteppedFormErrorsException([])));
        $this->assertFalse($this->normalizer->supportsNormalization(new StepIsNotSubmittedException($step)));
        $this->assertFalse($this->normalizer->supportsNormalization(new SteppedFormException()));
    }

    public function testNormalize(): void
    {
        $expected = new Response(status: Response::HTTP_NOT_FOUND);

        $actual = $this->normalizer->normalize(
            new StepNotFoundException('test'),
            new ExceptionDefinition(new FormSettings(), new StepsCollection([])),
        );

        $this->assertEquals($expected, $actual);
    }

    protected function setUp(): void
    {
        $this->normalizer = new StepNotFoundExceptionNormalizer();

        parent::setUp();
    }
}
