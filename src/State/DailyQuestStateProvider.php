<?php

namespace App\State;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\DailyQuest;
use App\ApiResource\QuestTreasure;
use App\Enum\DailyQuestStatusEnum;
use App\Repository\DragonTreasureRepository;

class DailyQuestStateProvider implements ProviderInterface
{
    public function __construct(
        private readonly DragonTreasureRepository $dragonTreasureRepository,
        private readonly Pagination               $pagination,
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $quests = $this->createQuests();

        if ($operation instanceof CollectionOperationInterface) {
            $currentPage = $this->pagination->getPage($context);
            $itemsPerPage = $this->pagination->getLimit($operation, $context);
            $offset = $this->pagination->getOffset($operation, $context);
            $totalItems = count($quests);
            $quests = $this->createQuests($offset, $itemsPerPage, $totalItems);
            return new TraversablePaginator(
                new \ArrayIterator($quests),
                $currentPage,
                $itemsPerPage,
                $totalItems,
            );
        }

        return $quests[$uriVariables['dayString']] ?? null;
    }

    private function createQuests(int $offset = 0, int $limit = 50, int $totalQuests = 50): array
    {
        $treasures = $this->dragonTreasureRepository->findBy([], limit: 10);

        $quests = [];
        for ($i = $offset; $i < ($offset + $limit) && $i < $totalQuests; $i++) {
            $quest = new DailyQuest(new \DateTimeImmutable(sprintf('- %d days', $i)));
            $quest->questName = sprintf('Quest %d', $i);
            $quest->description = sprintf('Description %d', $i);
            $quest->difficultyLevel = $i % 10;
            $quest->status = $i % 2 === 0 ? DailyQuestStatusEnum::ACTIVE : DailyQuestStatusEnum::COMPLETED;
            $quest->lastUpdated = new \DateTimeImmutable(sprintf('- %d days', random_int(1, 100)));

            // Assign a random treasure
            $randomTreasure = $treasures[array_rand($treasures)];
            $quest->treasure = new QuestTreasure(
                $randomTreasure->getName(),
                $randomTreasure->getValue(),
                $randomTreasure->getCoolFactor(),
            );

            $quests[$quest->getDayString()] = $quest;
        }

        return $quests;
    }
}
