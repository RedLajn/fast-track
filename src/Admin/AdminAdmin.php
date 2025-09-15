<?php

namespace App\Admin;

use App\Entity\Admin;
use App\Service\AdminWorkflowService;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AdminAdmin extends AbstractAdmin
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private AdminWorkflowService $workflowService
    ) {
        parent::__construct();
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->add('activate', $this->getRouterIdParameter().'/activate');
        $collection->add('suspend', $this->getRouterIdParameter().'/suspend');
        $collection->add('reactivate', $this->getRouterIdParameter().'/reactivate');
        $collection->add('reject', $this->getRouterIdParameter().'/reject');
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $isEdit = $this->isCurrentRoute('edit');
        $admin = $this->getSubject();

        $form
            ->add('username')
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'User' => 'ROLE_USER',
                    'Admin' => 'ROLE_ADMIN',
                    'Super Admin' => 'ROLE_SUPER_ADMIN',
                ],
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('plainPassword', PasswordType::class, [
                'required' => !$isEdit,
                'mapped' => false,
                'label' => 'Password',
                'help' => $isEdit ? 'Leave empty to keep current password' : 'Enter a password'
            ])
            ->add('notes', TextareaType::class, [
                'required' => false,
                'help' => 'Administrative notes about this user'
            ]);

        // Show workflow state in edit mode
        if ($isEdit && $admin) {
            $form->add('state', ChoiceType::class, [
                'choices' => [
                    'Pending' => 'pending',
                    'Active' => 'active',
                    'Suspended' => 'suspended',
                    'Rejected' => 'rejected',
                ],
                'disabled' => true,
                'help' => 'State is managed through workflow transitions'
            ]);
        }
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('username')
            ->add('state', null, [
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => [
                        'Pending' => 'pending',
                        'Active' => 'active',
                        'Suspended' => 'suspended',
                        'Rejected' => 'rejected',
                    ],
                ]
            ])
            ->add('roles');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id')
            ->add('username')
            ->add('state', 'choice', [
                'choices' => [
                    'pending' => 'Pending',
                    'active' => 'Active',
                    'suspended' => 'Suspended',
                    'rejected' => 'Rejected',
                ],
                'template' => 'admin/workflow_state.html.twig'
            ])
            ->add('createdAt', 'datetime', [
                'format' => 'Y-m-d H:i'
            ])
            ->add('activatedAt', 'datetime', [
                'format' => 'Y-m-d H:i'
            ])
            ->add('roles')
            ->add('_actions', null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                    'workflow' => [
                        'template' => 'admin/workflow_actions.html.twig'
                    ]
                ]
            ]);
    }

    public function prePersist(object $object): void
    {
        $this->updatePassword($object);
    }

    public function preUpdate(object $object): void
    {
        $this->updatePassword($object);
    }

    private function updatePassword(Admin $admin): void
    {
        $form = $this->getForm();

        if ($form->has('plainPassword')) {
            $plainPassword = $form->get('plainPassword')->getData();

            if (!empty($plainPassword)) {
                $hashedPassword = $this->passwordHasher->hashPassword($admin, $plainPassword);
                $admin->setPassword($hashedPassword);
            }
        }
    }

    public function getWorkflowService(): AdminWorkflowService
    {
        return $this->workflowService;
    }
}