<?php

namespace App\Mapper;

use App\ApiResource\DragonTreasureApi;
use App\ApiResource\UserApi;
use App\Entity\DragonTreasure;
use App\Entity\User;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

#[AsMapper(from: User::class, to: UserApi::class)]
class UserEntityToApiMapper implements MapperInterface
{
    public function __construct(private readonly MicroMapperInterface $microMapper)
    {
    }

    public function load(object $from, string $toClass, array $context): object
    {
        $entity = $from;

        $dto = new UserApi();
        $dto->id = $entity->getId();

        return $dto;
    }

    public function populate(object $from, object $to, array $context): object
    {
        $entity = $from;
        $dto = $to;

        $dto->email = $entity->getEmail();
        $dto->username = $entity->getUsername();
        $dto->flameThrowingDistance = random_int(1, 100);

        $dto->dragonTreasures = array_map(function (DragonTreasure $dragonTreasure) {
            return $this->microMapper->map($dragonTreasure, DragonTreasureApi::class, [
                MicroMapperInterface::MAX_DEPTH => 0,
            ]);
        }, $entity->getPublishedDragonTreasures()->getValues());

        return $dto;
    }
}
