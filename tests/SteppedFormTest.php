<?php

declare(strict_types=1);

namespace Lexal\HttpSteppedForm\Tests;

use Lexal\HttpSteppedForm\ExceptionNormalizer\ExceptionNormalizerInterface;
use Lexal\HttpSteppedForm\Renderer\RendererInterface;
use Lexal\HttpSteppedForm\Routing\RedirectorInterface;
use Lexal\HttpSteppedForm\SteppedForm;
use Lexal\HttpSteppedForm\SteppedFormInterface;
use Lexal\SteppedForm\Exception\AlreadyStartedException;
use Lexal\SteppedForm\Exception\FormNotStartedException;
use Lexal\SteppedForm\Exception\StepNotRenderableException;
use Lexal\SteppedForm\Exception\SteppedFormErrorsException;
use Lexal\SteppedForm\Step\StepKey;
use Lexal\SteppedForm\Step\TemplateDefinition;
use Lexal\SteppedForm\SteppedFormInterface as BaseSteppedFormInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class SteppedFormTest extends TestCase
{
    private RendererInterface&MockObject $renderer;
    private RedirectorInterface&MockObject $redirector;
    private BaseSteppedFormInterface&MockObject $baseForm;
    private ExceptionNormalizerInterface&Stub $exceptionNormalizer;
    private SteppedFormInterface $form;

    protected function setUp(): void
    {
        $this->renderer = $this->createMock(RendererInterface::class);
        $this->redirector = $this->createMock(RedirectorInterface::class);
        $this->baseForm = $this->createMock(BaseSteppedFormInterface::class);
        $this->exceptionNormalizer = $this->createStub(ExceptionNormalizerInterface::class);

        $this->form = new SteppedForm(
            $this->baseForm,
            new FormSettings(),
            $this->redirector,
            $this->renderer,
            $this->exceptionNormalizer,
        );
    }

    #[DataProvider('startDataProvider')]
    public function testStart(?StepKey $key, ?string $expectedUrl): void
    {
        $entity = new stdClass();
        $entity->name = 'test';

        $this->baseForm->expects(self::once())
            ->method('start')
            ->with($entity, '__MAIN__')
            ->willReturn($key);

        $this->redirector->expects(self::once())
            ->method('redirect')
            ->with($expectedUrl)
            ->willReturn(new Response());

        self::assertEquals(new Response(), $this->form->start($entity));
    }

    /**
     * @return iterable<string, array<StepKey|null|string>>
     */
    public static function startDataProvider(): iterable
    {
        yield 'with renderable step' => [new StepKey('key'), 'key'];
        yield 'without renderable step' => [null, 'finish'];
    }

    public function testStartException(): void
    {
        $exception = new AlreadyStartedException('test');

        $this->baseForm->expects(self::once())
            ->method('start')
            ->willThrowException($exception);

        $this->exceptionNormalizer->method('normalize')
            ->willReturn(new Response());

        self::assertEquals(new Response(), $this->form->start(new stdClass()));
    }

    public function testRender(): void
    {
        $templateDefinition = new TemplateDefinition('template', []);

        $this->baseForm->expects(self::once())
            ->method('render')
            ->with(new StepKey('test'))
            ->willReturn($templateDefinition);

        $this->renderer->expects(self::once())
            ->method('render')
            ->with($templateDefinition)
            ->willReturn(new Response());

        self::assertEquals(new Response(), $this->form->render('test'));
    }

    public function testRenderException(): void
    {
        $exception = new StepNotRenderableException(new StepKey('test'));

        $this->baseForm->expects(self::once())
            ->method('render')
            ->willThrowException($exception);

        $this->exceptionNormalizer->method('normalize')
            ->willReturn(new Response());

        self::assertEquals(new Response(), $this->form->render('test'));
    }

    #[DataProvider('handleDataProvider')]
    public function testHandle(?StepKey $key, ?string $expectedUrl): void
    {
        $this->baseForm->expects(self::once())
            ->method('handle')
            ->with(
                new StepKey('test'),
                [
                    'query' => 'query',
                    'request' => 'request',
                    'shared' => 'request',
                    'file' => new UploadedFile('path', 'original', error: UPLOAD_ERR_INI_SIZE),
                ],
            )
            ->willReturn($key);

        $this->redirector->expects(self::once())
            ->method('redirect')
            ->with($expectedUrl)
            ->willReturn(new Response());

        $request = new Request(
            ['query' => 'query', 'shared' => 'query'],
            ['request' => 'request', 'shared' => 'request'],
            files: ['file' => new UploadedFile('path', 'original', error: UPLOAD_ERR_INI_SIZE)],
        );

        self::assertEquals(new Response(), $this->form->handle('test', $request));
    }

    /**
     * @return iterable<string, array<StepKey|null|string>>
     */
    public static function handleDataProvider(): iterable
    {
        yield 'with renderable step' => [new StepKey('key'), 'key'];
        yield 'without renderable step' => [null, 'finish'];
    }

    public function testHandleException(): void
    {
        $exception = new SteppedFormErrorsException([]);

        $this->baseForm->expects(self::once())
            ->method('handle')
            ->willThrowException($exception);

        $this->exceptionNormalizer->method('normalize')
            ->willReturn(new Response());

        self::assertEquals(new Response(), $this->form->handle('test', new Request()));
    }

    public function testCancel(): void
    {
        $this->baseForm->expects(self::once())
            ->method('cancel');

        $this->redirector->expects(self::once())
            ->method('redirect')
            ->with('cancel')
            ->willReturn(new Response());

        self::assertEquals(new Response(), $this->form->cancel('cancel'));
    }

    public function testCancelException(): void
    {
        $exception = new FormNotStartedException();

        $this->baseForm->expects(self::once())
            ->method('cancel')
            ->willThrowException($exception);

        $this->exceptionNormalizer->method('normalize')
            ->willReturn(new Response());

        self::assertEquals(new Response(), $this->form->cancel('cancel'));
    }
}
