<?php

namespace App\Service;

use App\Entity\Admin;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Workflow\WorkflowInterface;

class AdminWorkflowService
{
    public function __construct(
        private WorkflowInterface $adminManagementStateMachine,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger
    ) {
    }

    public function activateAdmin(Admin $admin, ?string $notes = null): bool
    {
        if (!$this->adminManagementStateMachine->can($admin, 'activate')) {
            $this->logger->warning('Cannot activate admin', ['admin_id' => $admin->getId(), 'current_state' => $admin->getState()]);
            return false;
        }

        $this->adminManagementStateMachine->apply($admin, 'activate');
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
        if (!$this->adminManagementStateMachine->can($admin, 'suspend')) {
            $this->logger->warning('Cannot suspend admin', ['admin_id' => $admin->getId(), 'current_state' => $admin->getState()]);
            return false;
        }

        $this->adminManagementStateMachine->apply($admin, 'suspend');
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
        if (!$this->adminManagementStateMachine->can($admin, 'reactivate')) {
            $this->logger->warning('Cannot reactivate admin', ['admin_id' => $admin->getId(), 'current_state' => $admin->getState()]);
            return false;
        }

        $this->adminManagementStateMachine->apply($admin, 'reactivate');
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
        if (!$this->adminManagementStateMachine->can($admin, 'reject')) {
            $this->logger->warning('Cannot reject admin', ['admin_id' => $admin->getId(), 'current_state' => $admin->getState()]);
            return false;
        }

        $this->adminManagementStateMachine->apply($admin, 'reject');
        if ($reason) {
            $admin->setNotes($reason);
        }

        $this->entityManager->flush();
        $this->logger->info('Admin rejected', ['admin_id' => $admin->getId(), 'reason' => $reason]);

        return true;
    }

    public function getAvailableTransitions(Admin $admin): array
    {
        return $this->adminManagementStateMachine->getEnabledTransitions($admin);
    }

    public function canApplyTransition(Admin $admin, string $transition): bool
    {
        return $this->adminManagementStateMachine->can($admin, $transition);
    }

    public function getWorkflowDefinition(): array
    {
        return [
            'places' => $this->adminManagementStateMachine->getDefinition()->getPlaces(),
            'transitions' => $this->adminManagementStateMachine->getDefinition()->getTransitions()
        ];
    }
}