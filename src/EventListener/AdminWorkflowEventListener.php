<?php

namespace App\EventListener;

use App\Entity\Admin;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Symfony\Component\Workflow\Event\EnterEvent;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\Event\LeaveEvent;

class AdminWorkflowEventListener
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    #[AsEventListener(event: 'workflow.admin_management.guard')]
    public function guardTransition(GuardEvent $event): void
    {
        /** @var Admin $admin */
        $admin = $event->getSubject();
        $transitionName = $event->getTransition()->getName();

        switch ($transitionName) {
            case 'activate':
                if (empty($admin->getUsername()) || empty($admin->getPassword())) {
                    $event->setBlocked(true, 'Admin must have username and password to be activated');
                }
                break;

            case 'suspend':
                if (!$admin->isActive()) {
                    $event->setBlocked(true, 'Only active admins can be suspended');
                }
                break;

            case 'reject':
                break;
        }

        $this->logger->debug('Workflow guard checked', [
            'admin_id' => $admin->getId(),
            'transition' => $transitionName,
            'blocked' => $event->isBlocked()
        ]);
    }

    #[AsEventListener(event: 'workflow.admin_management.leave')]
    public function onLeave(LeaveEvent $event): void
    {
        /** @var Admin $admin */
        $admin = $event->getSubject();
        $transition = $event->getTransition();

        $this->logger->info('Admin leaving state', [
            'admin_id' => $admin->getId(),
            'from_state' => implode(', ', $transition->getFroms()),
            'via_transition' => $transition->getName()
        ]);
    }

    #[AsEventListener(event: 'workflow.admin_management.enter')]
    public function onEnter(EnterEvent $event): void
    {
        /** @var Admin $admin */
        $admin = $event->getSubject();
        $transition = $event->getTransition();

        $this->logger->info('Admin entering state', [
            'admin_id' => $admin->getId(),
            'to_state' => $transition->getTos()[0],
            'via_transition' => $transition->getName()
        ]);

        switch ($transition->getTos()[0]) {
            case 'active':
                $this->logger->info('Admin activated', ['admin_id' => $admin->getId()]);
                break;

            case 'suspended':
                $this->logger->info('Admin suspended', ['admin_id' => $admin->getId()]);
                break;

            case 'rejected':
                $this->logger->info('Admin rejected', ['admin_id' => $admin->getId()]);
                break;
        }
    }

    #[AsEventListener(event: 'workflow.admin_management.completed')]
    public function onCompleted(CompletedEvent $event): void
    {
        /** @var Admin $admin */
        $admin = $event->getSubject();
        $transition = $event->getTransition();

        $this->logger->info('Workflow transition completed', [
            'admin_id' => $admin->getId(),
            'transition' => $transition->getName(),
            'new_state' => $admin->getState()
        ]);

    }
}