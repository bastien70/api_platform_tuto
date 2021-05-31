<?php


namespace App\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInitializerInterface;
use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use ApiPlatform\Core\Serializer\AbstractItemNormalizer;
use ApiPlatform\Core\Validator\ValidatorInterface;
use App\Dto\CheeseListingInput;
use App\Entity\CheeseListing;

class CheeseListingInputDataTransformer implements DataTransformerInitializerInterface
{
    public function __construct(private ValidatorInterface $validator){}

    public function initialize(string $inputClass, array $context = [])
    {
        return $this->createDto($context);
    }

    /**
     * @param CheeseListingInput $input
     */
    public function transform($input, string $to, array $context = [])
    {
        $this->validator->validate($input); //Apply validation constraints
        $cheeseListing = $context[AbstractItemNormalizer::OBJECT_TO_POPULATE] ?? null;
        return $input->createOrUpdateEntity($cheeseListing);
    }

    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        if($data instanceof CheeseListing) {
            // already transformed
            return false;
        }

        return $to === CheeseListing::class && ($context['input']['class'] ?? null) === CheeseListingInput::class;
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