<?php

namespace App\State;

use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\DragonTreasure;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class DragonTreasureStateProvider implements ProviderInterface
{
    public function __construct(
        private readonly Security                                                          $security,
        #[Autowire(service: ItemProvider::class)] private readonly ProviderInterface       $itemProvider,
        #[Autowire(service: CollectionProvider::class)] private readonly ProviderInterface $collectionProvider,
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof CollectionOperationInterface) {
            return $this->collectionProvider($operation, $uriVariables, $context);
        }

        return $this->itemProvider($operation, $uriVariables, $context);
    }

    private function collectionProvider(Operation $operation, array $uriVariables, array $context): mixed
    {
        $paginator = $this->collectionProvider->provide($operation, $uriVariables, $context);

        foreach ($paginator as $dragonTreasure) {
            $dragonTreasure->setIsOwnedByAuthenticatedUser($dragonTreasure->getOwner() === $this->security->getUser());
        }

        return $paginator;
    }

    private function itemProvider(Operation $operation, array $uriVariables, array $context): mixed
    {
        $data = $this->itemProvider->provide($operation, $uriVariables, $context);

        if ($data instanceof DragonTreasure) {
            $data->setIsOwnedByAuthenticatedUser($data->getOwner() === $this->security->getUser());
        }

        return $data;
    }
}
