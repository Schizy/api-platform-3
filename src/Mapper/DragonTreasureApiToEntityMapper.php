<?php

namespace App\Mapper;

use App\ApiResource\DragonTreasureApi;
use App\Entity\DragonTreasure;
use App\Entity\User;
use App\Repository\DragonTreasureRepository;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

#[AsMapper(from: DragonTreasureApi::class, to: DragonTreasure::class)]
class DragonTreasureApiToEntityMapper implements MapperInterface
{
    public function __construct(
        private readonly DragonTreasureRepository $repository,
        private readonly Security                 $security,
        private readonly MicroMapperInterface     $microMapper,
    )
    {
    }

    public function load(object $from, string $toClass, array $context): object
    {
        $dto = $from;
        $entity = $dto->id ? $this->repository->find($dto->id) : new DragonTreasure($dto->name);
        if (!$entity) {
            throw new Exception('DragonTreasure not found');
        }

        return $entity;
    }

    public function populate(object $from, object $to, array $context): object
    {
        $dto = $from;
        $entity = $to;

        if ($dto->owner) {
            // We always need to set the max depth to 0 when we map a RELATION object, we just want to query it
            $entity->setOwner($this->microMapper->map($dto->owner, User::class, [
                MicroMapperInterface::MAX_DEPTH => 0,
            ]));
        } else {
            $entity->setOwner($this->security->getUser());
        }

        $entity->setDescription($dto->description);
        $entity->setCoolFactor($dto->coolFactor);
        $entity->setValue($dto->value);
        $entity->setIsPublished($dto->isPublished);

        return $entity;
    }
}
