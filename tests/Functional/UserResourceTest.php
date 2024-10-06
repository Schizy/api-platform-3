<?php

namespace App\Tests\Functional;

use App\Factory\DragonTreasureFactory;
use App\Factory\UserFactory;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class UserResourceTest extends ApiTestCase
{
    use ResetDatabase;
    use Factories;

    public function testPostToCreateUser(): void
    {
        $this->browser()
            ->post('/api/users', [
                'json' => [
                    'email' => 'draggin_in_the_morning@coffee.com',
                    'username' => 'draggin_in_the_morning',
                    'password' => 'password',
                ],
            ])
            ->assertStatus(201)
            ->post('/login', [
                'json' => [
                    'email' => 'draggin_in_the_morning@coffee.com',
                    'password' => 'password',
                ],
            ])
            ->assertSuccessful();
    }

    public function testPatchToUpdateUser(): void
    {
        $user = UserFactory::createOne();

        $this->browser()
            ->actingAs($user)
            ->patch('/api/users/' . $user->getId(), [
                'json' => [
                    'username' => 'changed',
                ],
                'headers' => ['Content-Type' => 'application/merge-patch+json'],
            ])
            ->assertStatus(200);
    }

    public function testTreasuresCannotBeStolen(): void
    {
        $user = UserFactory::createOne();
        $otherUser = UserFactory::createOne();
        $dragonTreasure = DragonTreasureFactory::createOne(['owner' => $otherUser]);

        $this->browser()
            ->actingAs($user)
            ->patch('/api/users/' . $user->getId(), [
                'json' => [
                    'username' => 'changed',
                    'dragonTreasures' => [ //It's easier to make dragonTreasures not writeable here and PATCH the treasures directly
                        '/api/treasures/' . $dragonTreasure->getId(),
                    ],
                ],
                'headers' => ['Content-Type' => 'application/merge-patch+json'],
            ])
            ->assertStatus(422);
    }

    public function testTreasuresCanBeRemoved(): void
    {
        $user = UserFactory::createOne();
        $dragonTreasure1 = DragonTreasureFactory::createOne(['owner' => $user]);
        DragonTreasureFactory::createOne(['owner' => $user]);

        $otherUser = UserFactory::createOne();
        $dragonTreasure3 = DragonTreasureFactory::createOne(['owner' => $otherUser]);

        $this->browser()
            ->actingAs($user)
            ->patch('/api/users/' . $user->getId(), [
                'json' => [
                    'dragonTreasures' => [
                        '/api/treasures/' . $dragonTreasure1->getId(),
                        '/api/treasures/' . $dragonTreasure3->getId(),
                    ],
                ],
                'headers' => ['Content-Type' => 'application/merge-patch+json'],
            ])
            ->assertStatus(200)
            ->get('/api/users/' . $user->getId())
            ->assertJsonMatches('length("dragonTreasures")', 2)
            ->assertJsonMatches('dragonTreasures[0]', '/api/treasures/' . $dragonTreasure1->getId())

            //Thanks to the PropertyAccessor that removed the 2nd treasure and set the 3rd automatically we're good here!
            // The 2nd one is even deleted from DB entirely because of the orphanRemoval: true
            ->assertJsonMatches('dragonTreasures[1]', '/api/treasures/' . $dragonTreasure3->getId());
    }

    public function testUnpublishedTreasuresNotReturned(): void
    {
        $user = UserFactory::createOne();
        DragonTreasureFactory::createOne([
            'isPublished' => false,
            'owner' => $user,
        ]);

        $this->browser()
            ->actingAs(UserFactory::createOne())
            ->get('/api/users/' . $user->getId())
            ->assertJsonMatches('length("dragonTreasures")', 0);
    }
}
