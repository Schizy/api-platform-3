<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityNotFoundException;

class EntityClassDtoStateProcessor implements ProcessorInterface
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        $entity = $this->mapDtoToEntity($data);
    }

    private function mapDtoToEntity(object $dto): object
    {
        //We edit an existing entity
        $entity = $dto->id ? $this->userRepository->find($dto->id) : new User();

        if (!$entity) {
            throw new \Exception(sprintf('Entity %d not found', $dto->id));
        }

        $entity->setEmail($dto->email);
        $entity->setUsername($dto->username);
        $entity->setPassword('TODO properly');
        // TODO: handle dragon treasures

        return $entity;
    }
}
