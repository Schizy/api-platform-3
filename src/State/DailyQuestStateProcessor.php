<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\DailyQuest;

class DailyQuestStateProcessor implements ProcessorInterface
{
    /**
     * @param DailyQuest $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        $data->lastUpdatedAt = new \DateTimeImmutable();
    }
}
