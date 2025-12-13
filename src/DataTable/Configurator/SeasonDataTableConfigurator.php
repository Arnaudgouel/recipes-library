<?php

namespace App\DataTable\Configurator;

use App\DataTable\Builder\DataTableBuilder;
use App\DataTable\DataTableFactory;
use App\Entity\Season;

class SeasonDataTableConfigurator implements DataTableConfiguratorInterface
{
    public function __construct(
        private readonly DataTableFactory $dataTableFactory
    ) {
    }

    public function supports(string $entityClass): bool
    {
        return $entityClass === Season::class;
    }

    public function configure(): void
    {
        $builder = new DataTableBuilder('admin_season', Season::class);

        $builder
            ->addField('name', 'string', [
                'label' => 'Nom',
                'sortable' => true,
            ])
            
            ->addFilter('name', 'string', [
                'label' => 'Nom',
            ])
            
            ->addAction('edit', [
                'label' => 'Modifier',
                'route' => 'admin_season_edit',
                'route_params' => ['id' => '{id}'],
                'css_class' => 'btn btn-sm btn-warning',
            ])
            ->addAction('delete', [
                'label' => 'Supprimer',
                'route' => 'admin_season_delete',
                'route_params' => ['id' => '{id}'],
                'css_class' => 'btn btn-sm btn-danger',
                'attributes' => [
                    'onclick' => "return confirm('Êtes-vous sûr ?')",
                ],
            ]);

        $this->dataTableFactory->getDataTableRegistry()->register($builder->getConfig());
    }
}

