<?php

namespace App\Admin;

use App\Entity\Admin;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AdminAdmin extends AbstractAdmin
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $isEdit = $this->isCurrentRoute('edit');

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
            ]);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('username')
            ->add('roles');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id')
            ->add('username')
            ->add('roles')
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
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
}