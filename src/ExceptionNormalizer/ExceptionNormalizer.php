<?php

declare(strict_types=1);

namespace Lexal\HttpSteppedForm\ExceptionNormalizer;

use InvalidArgumentException;
use Lexal\HttpSteppedForm\Exception\EmptyNormalizersException;
use Lexal\HttpSteppedForm\Exception\NormalizerNotFoundException;
use Lexal\HttpSteppedForm\Settings\FormSettingsInterface;
use Lexal\SteppedForm\Exception\SteppedFormException;
use Symfony\Component\HttpFoundation\Response;

use function get_class;
use function get_debug_type;
use function sprintf;

final class ExceptionNormalizer implements ExceptionNormalizerInterface
{
    /**
     * @var ExceptionNormalizerInterface[] $normalizers
     */
    private array $normalizers;

    /**
     * @var array<string, ExceptionNormalizerInterface|null>
     */
    private array $normalizersCache = [];

    /**
     * @param ExceptionNormalizerInterface[] $normalizers
     *
     * @throws EmptyNormalizersException
     */
    public function __construct(array $normalizers)
    {
        foreach ($normalizers as $normalizer) {
            if (!$normalizer instanceof ExceptionNormalizerInterface) {
                throw new InvalidArgumentException(
                    sprintf(
                        'The class [%s] must implement the [%s] interface.',
                        get_debug_type($normalizer),
                        ExceptionNormalizerInterface::class,
                    ),
                );
            }
        }

        if (!$normalizers) {
            throw new EmptyNormalizersException();
        }

        $this->normalizers = $normalizers;
    }

    public function supportsNormalization(SteppedFormException $exception): bool
    {
        return $this->getNormalizer($exception) !== null;
    }

    /**
     * @inheritDoc
     *
     * @throws NormalizerNotFoundException
     */
    public function normalize(SteppedFormException $exception, FormSettingsInterface $formSettings): Response
    {
        $normalizer = $this->getNormalizer($exception);

        if ($normalizer === null) {
            throw new NormalizerNotFoundException($exception);
        }

        return $normalizer->normalize($exception, $formSettings);
    }

    private function getNormalizer(SteppedFormException $exception): ?ExceptionNormalizerInterface
    {
        $class = get_class($exception);

        if (!isset($this->normalizersCache[$class])) {
            $this->normalizersCache[$class] = null;

            foreach ($this->normalizers as $normalizer) {
                if ($normalizer->supportsNormalization($exception)) {
                    $this->normalizersCache[$class] = $normalizer;
                    break;
                }
            }
        }

        return $this->normalizersCache[$class];
    }
}
