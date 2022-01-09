<?php

declare(strict_types=1);

namespace Lexal\HttpSteppedForm\Renderer;

use Lexal\SteppedForm\Entity\TemplateDefinition;
use Symfony\Component\HttpFoundation\Response;

interface RendererInterface
{
    /**
     * Renders a step by its definition.
     */
    public function render(TemplateDefinition $definition): Response;
}
