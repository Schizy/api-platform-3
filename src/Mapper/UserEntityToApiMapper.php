<?php

namespace App\Mapper;

use App\ApiResource\UserApi;
use App\Entity\User;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;

#[AsMapper(from: User::class, to: UserApi::class)]
class UserEntityToApiMapper implements MapperInterface
{
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
        $dto->dragonTreasures = $entity->getPublishedDragonTreasures()->getValues();
        $dto->flameThrowingDistance = random_int(1, 100);

        return $dto;
    }
}