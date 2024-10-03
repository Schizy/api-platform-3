<?php

namespace App\Mapper;

use App\ApiResource\DragonTreasureApi;
use App\ApiResource\UserApi;
use App\Entity\DragonTreasure;
use Symfony\Bundle\SecurityBundle\Security;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

#[AsMapper(from: DragonTreasure::class, to: DragonTreasureApi::class)]
class DragonTreasureEntityToApiMapper implements MapperInterface
{
    public function __construct(private readonly MicroMapperInterface $microMapper, private readonly Security $security)
    {
    }

    public function load(object $from, string $toClass, array $context): object
    {
        $entity = $from;

        return new DragonTreasureApi($entity->getId());
    }

    public function populate(object $from, object $to, array $context): object
    {
        $entity = $from;
        $dto = $to;

        $dto->name = $entity->getName();
        $dto->owner = $this->microMapper->map($entity->getOwner(), UserApi::class, [
            MicroMapperInterface::MAX_DEPTH => 0,
        ]);
        $dto->description = $entity->getDescription();
        $dto->value = $entity->getValue();
        $dto->coolFactor = $entity->getCoolFactor();
        $dto->shortDescription = $entity->getShortDescription();
        $dto->plunderedAtAgo = $entity->getPlunderedAtAgo();
        $dto->isMine = $this->security->getUser() && $this->security->getUser() === $entity->getOwner();

        return $dto;
    }
}
