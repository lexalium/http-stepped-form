<?php

declare(strict_types=1);

namespace Lexal\HttpSteppedForm\Renderer;

use Lexal\SteppedForm\Step\TemplateDefinition;
use Symfony\Component\HttpFoundation\Response;

interface RendererInterface
{
    /**
     * Translates a step to response by its template definition.
     */
    public function render(TemplateDefinition $definition): Response;
}
