<?php

namespace App\Service;

use App\Entity\Admin;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Workflow\Registry;

class AdminWorkflowService
{
    private $workflow;

    public function __construct(
        private Registry $workflowRegistry,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger
    ) {
    }

    private function getWorkflow(): \Symfony\Component\Workflow\WorkflowInterface
    {
        if (!$this->workflow) {
            $this->workflow = $this->workflowRegistry->get(new Admin(), 'admin_management');
        }
        return $this->workflow;
    }

    public function activateAdmin(Admin $admin, ?string $notes = null): bool
    {
        $workflow = $this->getWorkflow();

        if (!$workflow->can($admin, 'activate')) {
            $this->logger->warning('Cannot activate admin', ['admin_id' => $admin->getId(), 'current_state' => $admin->getState()]);
            return false;
        }

        $workflow->apply($admin, 'activate');
        $admin->setActivatedAt(new \DateTimeImmutable());
        if ($notes) {
            $admin->setNotes($notes);
        }

        $this->entityManager->flush();
        $this->logger->info('Admin activated', ['admin_id' => $admin->getId()]);

        return true;
    }

    public function suspendAdmin(Admin $admin, ?string $reason = null): bool
    {
        $workflow = $this->getWorkflow();

        if (!$workflow->can($admin, 'suspend')) {
            $this->logger->warning('Cannot suspend admin', ['admin_id' => $admin->getId(), 'current_state' => $admin->getState()]);
            return false;
        }

        $workflow->apply($admin, 'suspend');
        $admin->setSuspendedAt(new \DateTimeImmutable());
        if ($reason) {
            $admin->setNotes($reason);
        }

        $this->entityManager->flush();
        $this->logger->info('Admin suspended', ['admin_id' => $admin->getId(), 'reason' => $reason]);

        return true;
    }

    public function reactivateAdmin(Admin $admin, ?string $notes = null): bool
    {
        $workflow = $this->getWorkflow();

        if (!$workflow->can($admin, 'reactivate')) {
            $this->logger->warning('Cannot reactivate admin', ['admin_id' => $admin->getId(), 'current_state' => $admin->getState()]);
            return false;
        }

        $workflow->apply($admin, 'reactivate');
        $admin->setSuspendedAt(null);
        $admin->setActivatedAt(new \DateTimeImmutable());
        if ($notes) {
            $admin->setNotes($notes);
        }

        $this->entityManager->flush();
        $this->logger->info('Admin reactivated', ['admin_id' => $admin->getId()]);

        return true;
    }

    public function rejectAdmin(Admin $admin, ?string $reason = null): bool
    {
        $workflow = $this->getWorkflow();

        if (!$workflow->can($admin, 'reject')) {
            $this->logger->warning('Cannot reject admin', ['admin_id' => $admin->getId(), 'current_state' => $admin->getState()]);
            return false;
        }

        $workflow->apply($admin, 'reject');
        if ($reason) {
            $admin->setNotes($reason);
        }

        $this->entityManager->flush();
        $this->logger->info('Admin rejected', ['admin_id' => $admin->getId(), 'reason' => $reason]);

        return true;
    }

    public function restoreAdmin(Admin $admin, ?string $notes = null): bool
    {
        $workflow = $this->getWorkflow();

        if (!$workflow->can($admin, 'restore')) {
            $this->logger->warning('Cannot restore admin', ['admin_id' => $admin->getId(), 'current_state' => $admin->getState()]);
            return false;
        }

        $workflow->apply($admin, 'restore');
        if ($notes) {
            $admin->setNotes($notes);
        }

        $this->entityManager->flush();
        $this->logger->info('Admin restored', ['admin_id' => $admin->getId()]);

        return true;
    }

    public function getAvailableTransitions(Admin $admin): array
    {
        return $this->getWorkflow()->getEnabledTransitions($admin);
    }

    public function canApplyTransition(Admin $admin, string $transition): bool
    {
        return $this->getWorkflow()->can($admin, $transition);
    }

    public function getWorkflowDefinition(): array
    {
        $workflow = $this->getWorkflow();
        return [
            'places' => $workflow->getDefinition()->getPlaces(),
            'transitions' => $workflow->getDefinition()->getTransitions()
        ];
    }
}