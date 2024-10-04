<?php

namespace App\Security\Voter;

use App\ApiResource\DragonTreasureApi;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class DragonTreasureApiVoter extends Voter
{
    public const EDIT = 'EDIT';

    public function __construct(private readonly Security $security)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::EDIT
            && $subject instanceof DragonTreasureApi;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if (!$user = $token->getUser()) {
            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        if (!$this->security->isGranted('ROLE_TREASURE_EDIT')) {
            return false;
        }

        return $subject->owner?->id === $user->getId();
    }
}
