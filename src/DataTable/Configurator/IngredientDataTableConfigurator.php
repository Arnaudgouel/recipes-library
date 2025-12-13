<?php

namespace App\DataTable\Configurator;

use App\DataTable\Builder\DataTableBuilder;
use App\DataTable\DataTableFactory;
use App\Entity\Ingredient;

class IngredientDataTableConfigurator implements DataTableConfiguratorInterface
{
    public function __construct(
        private readonly DataTableFactory $dataTableFactory
    ) {
    }

    public function supports(string $entityClass): bool
    {
        return $entityClass === Ingredient::class;
    }

    public function configure(): void
    {
        $builder = new DataTableBuilder('admin_ingredient', Ingredient::class);

        $builder
            ->addField('name', 'string', [
                'label' => 'Nom',
                'sortable' => true,
            ])
            ->addField('normalizedName', 'string', [
                'label' => 'Nom normalisé',
                'sortable' => true,
                'visible' => false,
            ])
            
            ->addFilter('name', 'string', [
                'label' => 'Nom',
            ])
            
            ->addAction('edit', [
                'label' => 'Modifier',
                'route' => 'admin_ingredient_edit',
                'route_params' => ['id' => '{id}'],
                'css_class' => 'btn btn-sm btn-warning',
            ])
            ->addAction('delete', [
                'label' => 'Supprimer',
                'route' => 'admin_ingredient_delete',
                'route_params' => ['id' => '{id}'],
                'css_class' => 'btn btn-sm btn-danger',
                'attributes' => [
                    'onclick' => "return confirm('Êtes-vous sûr ?')",
                ],
            ]);

        $this->dataTableFactory->getDataTableRegistry()->register($builder->getConfig());
    }
}

