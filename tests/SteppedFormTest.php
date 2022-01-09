<?php

declare(strict_types=1);

namespace Lexal\HttpSteppedForm\Tests;

use Lexal\HttpSteppedForm\ExceptionNormalizer\Entity\ExceptionDefinition;
use Lexal\HttpSteppedForm\ExceptionNormalizer\ExceptionNormalizerInterface;
use Lexal\HttpSteppedForm\Renderer\RendererInterface;
use Lexal\HttpSteppedForm\Routing\RedirectorInterface;
use Lexal\HttpSteppedForm\SteppedForm;
use Lexal\HttpSteppedForm\SteppedFormInterface;
use Lexal\SteppedForm\Entity\TemplateDefinition;
use Lexal\SteppedForm\Exception\AlreadyStartedException;
use Lexal\SteppedForm\Exception\EntityNotFoundException;
use Lexal\SteppedForm\Exception\FormIsNotStartedException;
use Lexal\SteppedForm\Exception\StepNotRenderableException;
use Lexal\SteppedForm\Exception\SteppedFormErrorsException;
use Lexal\SteppedForm\Exception\SteppedFormException;
use Lexal\SteppedForm\SteppedFormInterface as BaseSteppedFormInterface;
use Lexal\SteppedForm\Steps\Collection\Step;
use Lexal\SteppedForm\Steps\Collection\StepsCollection;
use Lexal\SteppedForm\Steps\RenderStepInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SteppedFormTest extends TestCase
{
    private MockObject $renderer;
    private MockObject $redirector;
    private MockObject $baseForm;
    private MockObject $exceptionNormalizer;
    private SteppedFormInterface $form;

    public function testStart(): void
    {
        $step = new Step('test', $this->createMock(RenderStepInterface::class));

        $this->baseForm->expects($this->once())
            ->method('start')
            ->willReturn($step);

        $this->redirector->expects($this->once())
            ->method('redirect')
            ->with('test')
            ->willReturn(new Response());

        $this->assertEquals(new Response(), $this->form->start(['test']));
    }

    public function testStartException(): void
    {
        $exception = new AlreadyStartedException('test', null);

        $this->baseForm->expects($this->once())
            ->method('start')
            ->willThrowException($exception);

        $this->assertExceptionNormalizer($exception);

        $this->assertEquals(new Response(), $this->form->start(['test']));
    }

    public function testRender(): void
    {
        $templateDefinition = new TemplateDefinition('template', []);

        $this->baseForm->expects($this->once())
            ->method('render')
            ->with('test')
            ->willReturn($templateDefinition);

        $this->renderer->expects($this->once())
            ->method('render')
            ->with($templateDefinition)
            ->willReturn(new Response());

        $this->assertEquals(new Response(), $this->form->render('test'));
    }

    public function testRenderException(): void
    {
        $this->baseForm->expects($this->never())
            ->method('cancel');

        $this->assertRenderException(new StepNotRenderableException('test'));
    }

    public function testRenderEntityNotFoundException(): void
    {
        $this->baseForm->expects($this->once())
            ->method('cancel');

        $this->assertRenderException(new EntityNotFoundException('test'));
    }

    public function testHandle(): void
    {
        $step = new Step('test', $this->createMock(RenderStepInterface::class));

        $this->baseForm->expects($this->once())
            ->method('handle')
            ->with('test', [])
            ->willReturn($step);

        $this->redirector->expects($this->once())
            ->method('redirect')
            ->with('test')
            ->willReturn(new Response());

        $this->assertEquals(new Response(), $this->form->handle('test', new Request()));
    }

    public function testHandleNextStepNotFound(): void
    {
        $this->baseForm->expects($this->once())
            ->method('handle')
            ->with('test', [])
            ->willReturn(null);

        $this->redirector->expects($this->once())
            ->method('redirect')
            ->with('finish')
            ->willReturn(new Response());

        $this->assertEquals(new Response(), $this->form->handle('test', new Request()));
    }

    public function testHandleException(): void
    {
        $exception = new SteppedFormErrorsException([]);

        $this->baseForm->expects($this->once())
            ->method('handle')
            ->with('test', [])
            ->willThrowException($exception);

        $this->assertExceptionNormalizer($exception, 'test');

        $this->assertEquals(new Response(), $this->form->handle('test', new Request()));
    }

    public function testCancel(): void
    {
        $this->baseForm->expects($this->once())
            ->method('cancel');

        $this->redirector->expects($this->once())
            ->method('redirect')
            ->with('cancel')
            ->willReturn(new Response());

        $this->assertEquals(new Response(), $this->form->cancel('cancel'));
    }

    public function testCancelException(): void
    {
        $exception = new FormIsNotStartedException();

        $this->baseForm->expects($this->once())
            ->method('cancel')
            ->willThrowException($exception);

        $this->assertExceptionNormalizer($exception);

        $this->assertEquals(new Response(), $this->form->cancel('cancel'));
    }

    protected function setUp(): void
    {
        $this->renderer = $this->createMock(RendererInterface::class);
        $this->redirector = $this->createMock(RedirectorInterface::class);
        $this->baseForm = $this->createMock(BaseSteppedFormInterface::class);
        $this->exceptionNormalizer = $this->createMock(ExceptionNormalizerInterface::class);

        $this->form = new SteppedForm(
            $this->baseForm,
            new FormSettings(),
            $this->redirector,
            $this->renderer,
            $this->exceptionNormalizer,
        );

        parent::setUp();
    }

    private function assertRenderException(SteppedFormException $exception): void
    {
        $this->baseForm->expects($this->once())
            ->method('render')
            ->with('test')
            ->willThrowException($exception);

        $this->assertExceptionNormalizer($exception, 'test');

        $this->assertEquals(new Response(), $this->form->render('test'));
    }

    private function assertExceptionNormalizer(SteppedFormException $exception, ?string $key = null): void
    {
        $this->baseForm->expects($this->once())
            ->method('getSteps')
            ->willReturn(new StepsCollection([]));

        $this->exceptionNormalizer->expects($this->once())
            ->method('normalize')
            ->with($exception, new ExceptionDefinition(new FormSettings(), new StepsCollection([]), $key))
            ->willReturn(new Response());
    }
}
