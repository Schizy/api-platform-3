<?php

namespace App\Tests\Functional;

class DailyQuestResourceTest extends ApiTestCase
{
    public function testPatchCanUpdateStatus(): void
    {
        $day = new \DateTime('-2 day');

        $this->browser()
            ->patch('/api/quests/' . $day->format('Y-m-d'), [
                'json' => [
                    'status' => 'completed',
                ],
                'headers' => [
                    'Content-Type' => 'application/merge-patch+json',
                ]
            ])
            ->assertStatus(200)
            ->assertJsonMatches('status', 'completed');
    }
}
