<?php

namespace App\Mapper;

use App\ApiResource\UserApi;
use App\Entity\DragonTreasure;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

#[AsMapper(from: UserApi::class, to: User::class)]
class UserApiToEntityMapper implements MapperInterface
{
    public function __construct(
        private readonly UserRepository              $userRepository,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly MicroMapperInterface        $microMapper,
        private readonly PropertyAccessorInterface   $propertyAccessor,
    )
    {
    }

    public function load(object $from, string $toClass, array $context): object
    {
        $dto = $from;

        $userEntity = $dto->id ? $this->userRepository->find($dto->id) : new User();
        if (!$userEntity) {
            throw new \Exception('User not found');
        }

        return $userEntity;
    }

    public function populate(object $from, object $to, array $context): object
    {
        $dto = $from;
        $entity = $to;

        $entity->setEmail($dto->email);
        $entity->setUsername($dto->username);
        if ($dto->password) {
            $entity->setPassword($this->userPasswordHasher->hashPassword($entity, $dto->password));
        }

        // TODO dragonTreasures if we change them to writeable
        $dragonTreasureEntities = [];
        foreach ($dto->dragonTreasures as $dragonTreasureApi) {
            $dragonTreasureEntities[] = $this->microMapper->map($dragonTreasureApi, DragonTreasure::class, [
                MicroMapperInterface::MAX_DEPTH => 0,
            ]);
        }

        //The User doesn't have a setter for the DragonTreasures so we have to call addDragonTreasure (that set the owner) or removeDragonTreasure
        //We have to call the right one on each case but that's too boring so we let the propertyAccessor do it for us =)
        $this->propertyAccessor->setValue($entity, 'dragonTreasures', $dragonTreasureEntities);

        return $entity;
    }
}
