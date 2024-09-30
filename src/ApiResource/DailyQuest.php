<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use App\State\DailyQuestStateProvider;

#[ApiResource(
    shortName: 'Quest',
    provider: DailyQuestStateProvider::class,
)]
class DailyQuest
{
    public int $id;

    /**
     * @param int $id
     */
    public function __construct(int $id)
    {
        $this->id = $id;
    }
}