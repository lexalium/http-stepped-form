# Exception Normalizers

Exception Normalizers are used for the normalizing Stepped Form exceptions
into the Response instance. You can create new normalizers by implementing
`Lexal\HttpSteppedForm\ExceptionNormalizer\ExceptionNormalizerInterface`.

See 
[interface definition](../src/ExceptionNormalizer/ExceptionNormalizerInterface.php)
for more details.

The package already contains normalizers for all available exceptions:
1. `Lexal\HttpSteppedForm\ExceptionNormalizer\Normalizers\AlreadyStartedExceptionNormalizer`.
2. `Lexal\HttpSteppedForm\ExceptionNormalizer\Normalizers\EntityNotFoundExceptionNormalizer`.
3. `Lexal\HttpSteppedForm\ExceptionNormalizer\Normalizers\FormIsNotStartedExceptionNormalizer`.
4. `Lexal\HttpSteppedForm\ExceptionNormalizer\Normalizers\StepNotFoundExceptionNormalizer`.
5. `Lexal\HttpSteppedForm\ExceptionNormalizer\Normalizers\StepNotRenderableExceptionNormalizer`.
6. `Lexal\HttpSteppedForm\ExceptionNormalizer\Normalizers\SteppedFormErrorsExceptionNormalizer`.
7. `Lexal\HttpSteppedForm\ExceptionNormalizer\Normalizers\DefaultExceptionNormalizer`.
