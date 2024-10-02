<?php

namespace App\State;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Doctrine\Common\State\RemoveProcessor;
use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class EntityClassDtoStateProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: PersistProcessor::class)]
        private readonly ProcessorInterface          $persistProcessor,
        #[Autowire(service: RemoveProcessor::class)]
        private readonly ProcessorInterface          $removeProcessor,
        private readonly UserRepository              $userRepository,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $entity = $this->mapDtoToEntity($data);

        if ($operation instanceof DeleteOperationInterface) {
            $this->removeProcessor->process($entity, $operation, $uriVariables, $context);

            return null;
        }

        $this->persistProcessor->process($entity, $operation, $uriVariables, $context);
        $data->id = $entity->getId();

        return $data;
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
        if ($dto->password) {
            $entity->setPassword($this->userPasswordHasher->hashPassword($entity, $dto->password));
        }

        // TODO: handle dragon treasures

        return $entity;
    }
}
