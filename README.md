# HTTP based Stepped Form

[![PHPUnit, PHPCS, PHPStan Tests](https://github.com/lexalium/http-stepped-form/actions/workflows/tests.yml/badge.svg)](https://github.com/lexalium/http-stepped-form/actions/workflows/tests.yml)

The package is based on the [Stepped Form package](https://github.com/lexalium/stepped-form) and works with
HTTP response and requests (transforms form exception into Response and renders or redirects depending on base
form return value).

<a id="readme-top" mame="readme-top"></a>

Table of Contents

1. [Requirements](#requirements)
2. [Installation](#installation)
3. [Usage](#usage)
    - [Exception Normalizers](#exception-normalizers)
4. [License](#license)

---

## Requirements

**PHP:** >=8.1

## Installation

Via Composer

```
composer require lexal/http-stepped-form
```

## Usage

1. Create a base [Stepped Form](https://github.com/lexalium/stepped-form).
2. Declare your form settings.
   ```php
   use Lexal\HttpSteppedForm\Settings\FormSettingsInterface;
   use Lexal\SteppedForm\Step\StepKey;

   final class FormSettings implements FormSettingsInterface
   {
       public function getStepUrl(StepKey $key): string
       {
           // return step URL
       }
 
       public function getUrlBeforeStart(): string
       {
           // returns a URL to redirect to when there is no previously renderable step
       }

       public function getUrlAfterFinish(): string
       {
           // return a URL to redirect to when the form was finishing
       }
   }

   $formSettings = new FormSettings();
   ```

3. Create a Redirector. The Redirector creates and returns Redirect Response.
   ```php
   use Lexal\HttpSteppedForm\Routing\RedirectorInterface;
   use Symfony\Component\HttpFoundation\Response;

   final class Redirector implements RedirectorInterface
   {
       public function redirect(string $url, array $errors = []): Response
       {
           // create and return redirect response
       }
   }

   $redirector = new Redirector();
   ```

4. Create a Renderer. The Renderer crates and returns Response by template definition.
   ```php
   use Lexal\HttpSteppedForm\Renderer\RendererInterface;
   use Lexal\SteppedForm\Entity\TemplateDefinition;
   use Symfony\Component\HttpFoundation\Response;

   final class Renderer implements RendererInterface
   {
       public function render(TemplateDefinition $definition): Response
       {
           // create and return response
       }
   }

   $renderer = new Renderer();
   ```

5. Create an Exception Normalizer.
   ```php
   use Lexal\HttpSteppedForm\ExceptionNormalizer\ExceptionNormalizer;
   use Lexal\HttpSteppedForm\ExceptionNormalizer\Normalizers\AlreadyStartedExceptionNormalizer;
   use Lexal\HttpSteppedForm\ExceptionNormalizer\Normalizers\DefaultExceptionNormalizer;
   use Lexal\HttpSteppedForm\ExceptionNormalizer\Normalizers\EntityNotFoundExceptionNormalizer;
   use Lexal\HttpSteppedForm\ExceptionNormalizer\Normalizers\FormIsNotStartedExceptionNormalizer;
   use Lexal\HttpSteppedForm\ExceptionNormalizer\Normalizers\StepNotFoundExceptionNormalizer;
   use Lexal\HttpSteppedForm\ExceptionNormalizer\Normalizers\StepNotRenderableExceptionNormalizer;
   
   $normalizer = new ExceptionNormalizer([
       new AlreadyStartedExceptionNormalizer($redirector),
       new EntityNotFoundExceptionNormalizer($redirector),
       new FormIsNotStartedExceptionNormalizer($redirector),
       new StepNotRenderableExceptionNormalizer(),
       new StepNotFoundExceptionNormalizer(),
       new DefaultExceptionNormalizer(),
   ]);
   ```

6. Create a Stepped Form.
   ```php
   use Lexal\HttpSteppedForm\SteppedForm;

   $form = new SteppedForm(
       /* a base stepped form from the step 1 */,
       $formSettings,
       $redirector,
       $renderer,
       $normalizer,
   );
   ```

7. Use Stepped Form in your application.
   ```php
   /*
    * Starts a new form session.
    * Returns redirect response to the next step or URL after form finish.
    */
   $form->start(
       /* an entity to initialize a form state */,
       /* unique session key is you need to split different sessions of one form */,
   );

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

<div style="text-align: right">(<a href="#readme-top">back to top</a>)</div>

## Exception Normalizers

Exception Normalizers are used for the normalizing Stepped Form exceptions into the Response instance. Create class
that implements `ExceptionNormalizerInterface` to create your own exception normalizer.

```php
use Lexal\HttpSteppedForm\Settings\FormSettingsInterface;
use Lexal\HttpSteppedForm\ExceptionNormalizer\ExceptionNormalizerInterface;
use Lexal\SteppedForm\Exception\AlreadyStartedException;
use Lexal\SteppedForm\Exception\SteppedFormException;
use Symfony\Component\HttpFoundation\Response;

final class CustomExceptionNormalizer implements ExceptionNormalizerInterface
{
    public function supportsNormalization(SteppedFormException $exception): bool
    {
        return $exception instanceof AlreadyStartedException;
    }
    
    public function normalize(SteppedFormException $exception, FormSettingsInterface $formSettings): Response
    {
        // return custom response object
        return new Response();
    }
}
```

The package already contains normalizers for all available exceptions:
1. `AlreadyStartedExceptionNormalizer` - redirects to the current renderable step.
2. `EntityNotFoundExceptionNormalizer` - redirects with errors to the previously renderable step or the URL
   before form start.
3. `FormIsNotStartedExceptionNormalizer` - redirects with errors to the URL before form start.
4. `StepNotFoundExceptionNormalizer` - returns 404 HTTP status code.
5. `StepNotRenderableExceptionNormalizer` - returns 404 HTTP status code.
6. `SteppedFormErrorsExceptionNormalizer` - redirects with errors to the previously renderable step or the URL
   before form start.
7. `StepIsNotSubmittedExceptionNormalizer` - redirects with errors to the previously renderable step or the URL
   before form start.
8. `DefaultExceptionNormalizer` - rethrows exception.

<div style="text-align: right">(<a href="#readme-top">back to top</a>)</div>

---

## License

HTTP Stepped Form is licensed under the MIT License. See [LICENSE](LICENSE) for the full license text.
