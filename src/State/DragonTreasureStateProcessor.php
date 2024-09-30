<?php

namespace App\State;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\DragonTreasure;
use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class DragonTreasureStateProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: PersistProcessor::class)]
        private readonly ProcessorInterface     $persistProcessor,
        private readonly Security               $security,
        private readonly EntityManagerInterface $em,
    )
    {
    }

    /**
     * @param DragonTreasure $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        // Data we set before the persistence
        if ($data->getOwner() === null && $this->security->getUser()) {
            $data->setOwner($this->security->getUser());
        }

        $this->persistProcessor->process($data, $operation, $uriVariables, $context);

        // Data we set after the persistence
        $data->setIsOwnedByAuthenticatedUser($data->getOwner() === $this->security->getUser());
        $this->notifyWhenPublishingTreasure($context['previous_data'], $data);
    }

    private function notifyWhenPublishingTreasure($previous_data, mixed $data): void
    {
        $previousData = $previous_data ?? null;
        if ($data->getIsPublished() && (!$previousData || !$previousData->getIsPublished())) {
            $this->em->persist(
                (new Notification())
                    ->setDragonTreasure($data)
                    ->setMessage('Treasure has been published!'),
            );
            $this->em->flush();
        }
    }
}
