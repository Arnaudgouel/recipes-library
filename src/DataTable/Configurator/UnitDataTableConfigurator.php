<?php

namespace App\DataTable\Configurator;

use App\DataTable\Builder\DataTableBuilder;
use App\DataTable\DataTableFactory;
use App\Entity\Unit;

class UnitDataTableConfigurator implements DataTableConfiguratorInterface
{
    public function __construct(
        private readonly DataTableFactory $dataTableFactory
    ) {
    }

    public function supports(string $entityClass): bool
    {
        return $entityClass === Unit::class;
    }

    public function configure(): void
    {
        $builder = new DataTableBuilder('admin_unit', Unit::class);

        $builder
            ->addField('code', 'string', [
                'label' => 'Code',
                'sortable' => true,
            ])
            ->addField('label', 'string', [
                'label' => 'Libellé',
                'sortable' => true,
            ])
            ->addField('pluralLabel', 'string', [
                'label' => 'Libellé pluriel',
                'sortable' => true,
            ])
            ->addField('kind', 'string', [
                'label' => 'Type',
                'sortable' => true,
            ])
            
            ->addFilter('label', 'string', [
                'label' => 'Libellé',
            ])
            ->addFilter('kind', 'string', [
                'label' => 'Type',
            ])
            
            ->addAction('edit', [
                'label' => 'Modifier',
                'route' => 'admin_unit_edit',
                'route_params' => ['code' => '{code}'],
                'css_class' => 'btn btn-sm btn-warning',
            ])
            ->addAction('delete', [
                'label' => 'Supprimer',
                'route' => 'admin_unit_delete',
                'route_params' => ['code' => '{code}'],
                'css_class' => 'btn btn-sm btn-danger',
                'attributes' => [
                    'onclick' => "return confirm('Êtes-vous sûr ?')",
                ],
            ]);

        $this->dataTableFactory->getDataTableRegistry()->register($builder->getConfig());
    }
}

