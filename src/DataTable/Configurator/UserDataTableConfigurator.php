<?php

namespace App\DataTable\Configurator;

use App\DataTable\Builder\DataTableBuilder;
use App\DataTable\DataTableFactory;
use App\Entity\User;

class UserDataTableConfigurator implements DataTableConfiguratorInterface
{
    public function __construct(
        private readonly DataTableFactory $dataTableFactory
    ) {
    }

    public function supports(string $entityClass): bool
    {
        return $entityClass === User::class;
    }

    public function configure(): void
    {
        $builder = new DataTableBuilder('admin_user', User::class);

        $builder
            ->addField('email', 'string', [
                'label' => 'Email',
                'sortable' => true,
            ])
            
            ->addFilter('email', 'string', [
                'label' => 'Email',
            ])
            
            ->addAction('edit', [
                'label' => 'Modifier',
                'route' => 'admin_user_edit',
                'route_params' => ['id' => '{id}'],
                'css_class' => 'btn btn-sm btn-warning',
            ])
            ->addAction('delete', [
                'label' => 'Supprimer',
                'route' => 'admin_user_delete',
                'route_params' => ['id' => '{id}'],
                'css_class' => 'btn btn-sm btn-danger',
                'attributes' => [
                    'onclick' => "return confirm('Êtes-vous sûr ?')",
                ],
            ]);

        $this->dataTableFactory->getDataTableRegistry()->register($builder->getConfig());
    }
}

