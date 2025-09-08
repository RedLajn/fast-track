<?php

namespace App\Admin;

use App\Entity\Conference;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;

final class ConferenceAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('city')
            ->add('year')
            ->add('isInternational')
            ->add('slug');
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('city')
            ->add('year')
            ->add('isInternational');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id')
            ->add('city')
            ->add('year')
            ->add('isInternational')
            ->add('slug');
    }
}
