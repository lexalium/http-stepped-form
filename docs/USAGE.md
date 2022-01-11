# Usage

1. Create a base [Stepped Form](https://github.com/alexxxxkkk/stepped-form).
2. Declare your form settings.
   See [interface definition](../src/Settings/FormSettingsInterface.php)
   for more details.

```php
<?php

use Lexal\HttpSteppedForm\Settings\FormSettingsInterface;

class FormSettings implements FormSettingsInterface
{
    public function getStepUrl(string $key): string
    {
        // return step URL
    }
    
    public function getUrlBeforeStart(): string
    {
        // return a URL to redirect to if step entity is not found
    }
    
    public function getUrlAfterFinish(): string
    {
        // return a URL to redirect to when the form was finishing
    }
}

$formSettings = new FormSettings();
```

3. Create a Redirector instance. The Redirector creates and returns
   Redirect Response. See [interface definition](../src/Routing/RedirectorInterface.php)
   for more details.

```php
<?php

use Lexal\HttpSteppedForm\Routing\RedirectorInterface;
use Symfony\Component\HttpFoundation\Response;

class Redirector implements RedirectorInterface
{
    public function redirect(string $url, array $errors = []): Response
    {
        // create and return redirect response
    }
}

$redirector = new Redirector();
```

4. Create a Renderer instance. The Renderer crates and returns Response
   by template definition. See [interface definition](../src/Renderer/RendererInterface.php)
   for more details.

```php
<?php

use Lexal\HttpSteppedForm\Renderer\RendererInterface;
use Lexal\SteppedForm\Entity\TemplateDefinition;
use Symfony\Component\HttpFoundation\Response;

class Renderer implements RendererInterface
{
    public function render(TemplateDefinition $definition): Response
    {
        // create and return response
    }
}

$renderer = new Renderer();
```

5. Create an Exception Normalizer instance.

```php
<?php

use Lexal\HttpSteppedForm\ExceptionNormalizer\ExceptionNormalizer;
use Lexal\HttpSteppedForm\ExceptionNormalizer\Normalizers\AlreadyStartedExceptionNormalizer;
use Lexal\HttpSteppedForm\ExceptionNormalizer\Normalizers\DefaultExceptionNormalizer;
use Lexal\HttpSteppedForm\ExceptionNormalizer\Normalizers\EntityNotFoundExceptionNormalizer;
use Lexal\HttpSteppedForm\ExceptionNormalizer\Normalizers\FormIsNotStartedExceptionNormalizer;
use Lexal\HttpSteppedForm\ExceptionNormalizer\Normalizers\StepNotFoundExceptionNormalizer;
use Lexal\HttpSteppedForm\ExceptionNormalizer\Normalizers\StepNotRenderableExceptionNormalizer;

$normalizer = new ExceptionNormalizer(
    new AlreadyStartedExceptionNormalizer($redirector),
    new EntityNotFoundExceptionNormalizer($redirector),
    new FormIsNotStartedExceptionNormalizer($redirector),
    new StepNotRenderableExceptionNormalizer(),
    new StepNotFoundExceptionNormalizer(),
    new DefaultExceptionNormalizer(),
);
```

6. Create a Stepped Form instance.

```php
<?php

use Lexal\HttpSteppedForm\SteppedForm;

$form = new SteppedForm(
    /* a base stepped form from the point 1 */,
    $formSettings,
    $redirector,
    $renderer,
    $normalizer,
);
```

7. Use Stepped Form in your application.

```php
<?php

/*
 * Starts a new form session.
 * Returns redirect response to the next step or URL after form finish.
 */
$form->start(/* an entity for initializing a form state */);

/* Renders step by its definition */
$form->render('key');

/*
 * Handles a step logic and saves a new form state.
 * Returns redirect response to the next step or URL after form finish.
 */
$form->handle('key', /* request instance*/);

/* Cancels form session and returns redirect response to the given URL */
$form->cancel(/* any URL */);
```
