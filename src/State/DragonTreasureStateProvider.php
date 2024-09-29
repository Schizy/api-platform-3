<?php

namespace App\State;

use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\DragonTreasure;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

// I could decorate like usual, but we can also tell which service we want in the constructor
// And use it manually in the dragon treasure class
// #[AsDecorator('api_platform.doctrine.orm.state.item_provider')]
class DragonTreasureStateProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: ItemProvider::class)]
        private readonly ProviderInterface $itemProvider,
        private readonly Security          $security,
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $data = $this->itemProvider->provide($operation, $uriVariables, $context);

        if ($data instanceof DragonTreasure) {
            $data->setIsOwnedByAuthenticatedUser($data->getOwner() === $this->security->getUser());
        }

        return $data;
    }
}
