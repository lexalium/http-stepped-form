<?php

declare(strict_types=1);

namespace Lexal\HttpSteppedForm\Routing;

use Symfony\Component\HttpFoundation\Response;

interface RedirectorInterface
{
    /**
     * Redirects to the given url with errors (if passed).
     *
     * @param string[] $errors
     */
    public function redirect(string $url, array $errors = []): Response;
}
