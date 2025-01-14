<?php

namespace App\State;

use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use ArrayIterator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfonycasts\MicroMapper\MicroMapperInterface;

class EntityToDtoStateProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: CollectionProvider::class)] private readonly ProviderInterface $collectionProvider,
        #[Autowire(service: ItemProvider::class)] private readonly ProviderInterface       $itemProvider,
        private readonly MicroMapperInterface                                              $microMapper,
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $resourceClass = $operation->getClass();

        if ($operation instanceof CollectionOperationInterface) {
            $entities = $this->collectionProvider->provide($operation, $uriVariables, $context);

            $dtos = [];
            foreach ($entities as $entity) {
                $dtos[] = $this->mapEntityToDto($entity, $resourceClass);
            }

            return new TraversablePaginator(
                new ArrayIterator($dtos), $entities->getCurrentPage(), $entities->getItemsPerPage(), $entities->getTotalItems(),
            );
        }

        $entity = $this->itemProvider->provide($operation, $uriVariables, $context);

        return $entity ? $this->mapEntityToDto($entity, $resourceClass) : null;
    }

    private function mapEntityToDto(object $entity, string $resourceClass): object
    {
        return $this->microMapper->map($entity, $resourceClass);
    }
}
