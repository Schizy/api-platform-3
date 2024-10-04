<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Notification;
use App\Repository\DragonTreasureRepository;
use Doctrine\ORM\EntityManagerInterface;

class DragonTreasureStateProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityClassDtoStateProcessor $innerProcessor,
        private readonly EntityManagerInterface       $entityManager,
        private readonly DragonTreasureRepository     $repository,
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $previousData = $context['previous_data'] ?? null;
        if ($data->isPublished && $previousData?->isPublished !== $data->isPublished) {
            $notification = (new Notification())
                ->setDragonTreasure($this->repository->find($data->id))
                ->setMessage('Treasure has been published!');

            $this->entityManager->persist($notification);
            $this->entityManager->flush();
        }

        return $this->innerProcessor->process($data, $operation, $uriVariables, $context);;
    }
}
