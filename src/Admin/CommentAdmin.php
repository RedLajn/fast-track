<?php

namespace App\Admin;

use App\Entity\Comment;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;

final class CommentAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('author')
            ->add('email')
            ->add('text')
            ->add('state')
            ->add('conference'); // dropdown thanks to __toString() in Conference
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('author')
            ->add('email')
            ->add('state')
            ->add('conference');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id')
            ->add('author')
            ->add('email')
            ->add('state')
            ->add('conference');
    }
}