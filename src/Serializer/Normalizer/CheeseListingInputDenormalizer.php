<?php

namespace App\Serializer\Normalizer;

use ApiPlatform\Core\Serializer\AbstractItemNormalizer;
use App\Dto\CheeseListingInput;
use App\Entity\CheeseListing;
use Symfony\Component\Serializer\Exception\BadMethodCallException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\ExtraAttributesException;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Exception\RuntimeException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class CheeseListingInputDenormalizer
//class CheeseListingInputDenormalizer implements DenormalizerInterface, CacheableSupportsMethodInterface
{
    public function __construct(private ObjectNormalizer $objectNormalizer){}

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        $context[AbstractItemNormalizer::OBJECT_TO_POPULATE] = $this->createDto($context);

        return $this->objectNormalizer->denormalize($data, $type, $format, $context);
    }

    public function supportsDenormalization($data, string $type, string $format = null)
    {
        return $type === CheeseListingInput::class;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * @throws \Exception
     */
    private function createDto(array $context): CheeseListingInput
    {
        $entity = $context[AbstractItemNormalizer::OBJECT_TO_POPULATE] ?? null;

        if ($entity && !$entity instanceof CheeseListing) {
            throw new \Exception(sprintf('Unexpected resource class "%s"', get_class($entity)));
        }

        return CheeseListingInput::createFromEntity($entity);
    }
}