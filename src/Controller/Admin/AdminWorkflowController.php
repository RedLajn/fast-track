<?php

namespace App\Controller\Admin;

use App\Entity\Admin;
use App\Service\AdminWorkflowService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/workflow')]
class AdminWorkflowController extends AbstractController
{
    public function __construct(
        private AdminWorkflowService $workflowService
    ) {
    }

    #[Route('/admin/{id}/activate', name: 'admin_admin_activate', methods: ['POST'])]
    public function activate(Admin $admin, Request $request): Response
    {
        $notes = $request->request->get('notes');

        if ($this->workflowService->activateAdmin($admin, $notes)) {
            $this->addFlash('success', 'Admin has been activated successfully.');
        } else {
            $this->addFlash('error', 'Cannot activate this admin.');
        }

        return $this->redirectToRoute('admin_app_admin_list');
    }

    #[Route('/admin/{id}/suspend', name: 'admin_admin_suspend', methods: ['POST'])]
    public function suspend(Admin $admin, Request $request): Response
    {
        $reason = $request->request->get('reason');

        if ($this->workflowService->suspendAdmin($admin, $reason)) {
            $this->addFlash('success', 'Admin has been suspended.');
        } else {
            $this->addFlash('error', 'Cannot suspend this admin.');
        }

        return $this->redirectToRoute('admin_app_admin_list');
    }

    #[Route('/admin/{id}/reactivate', name: 'admin_admin_reactivate', methods: ['POST'])]
    public function reactivate(Admin $admin, Request $request): Response
    {
        $notes = $request->request->get('notes');

        if ($this->workflowService->reactivateAdmin($admin, $notes)) {
            $this->addFlash('success', 'Admin has been reactivated.');
        } else {
            $this->addFlash('error', 'Cannot reactivate this admin.');
        }

        return $this->redirectToRoute('admin_app_admin_list');
    }

    #[Route('/admin/{id}/reject', name: 'admin_admin_reject', methods: ['POST'])]
    public function reject(Admin $admin, Request $request): Response
    {
        $reason = $request->request->get('reason');

        if ($this->workflowService->rejectAdmin($admin, $reason)) {
            $this->addFlash('success', 'Admin has been rejected.');
        } else {
            $this->addFlash('error', 'Cannot reject this admin.');
        }

        return $this->redirectToRoute('admin_app_admin_list');
    }

    #[Route('/admin/{id}/restore', name: 'admin_admin_restore', methods: ['POST'])]
    public function restore(Admin $admin, Request $request): Response
    {
        $notes = $request->request->get('notes');

        if ($this->workflowService->restoreAdmin($admin, $notes)) {
            $this->addFlash('success', 'Admin has been restored.');
        } else {
            $this->addFlash('error', 'Cannot restore this admin.');
        }

        return $this->redirectToRoute('admin_app_admin_list');
    }
}