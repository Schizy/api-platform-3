<?php

namespace App\Mapper;

use App\ApiResource\DragonTreasureApi;
use App\Entity\DragonTreasure;
use App\Repository\DragonTreasureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;

#[AsMapper(from: DragonTreasureApi::class, to: DragonTreasure::class)]
class DragonTreasureApiToEntityMapper implements MapperInterface
{
    public function __construct(private readonly DragonTreasureRepository $repository, private readonly Security $security)
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
            //TODO
        } else {
            $entity->setOwner($this->security->getUser());
        }

        $entity->setDescription($dto->description);
        $entity->setCoolFactor($dto->coolFactor);
        $entity->setValue($dto->value);

        return $entity;
    }
}