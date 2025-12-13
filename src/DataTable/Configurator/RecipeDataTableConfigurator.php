<?php

namespace App\DataTable\Configurator;

use App\DataTable\Builder\DataTableBuilder;
use App\DataTable\DataTableFactory;
use App\Entity\Recipe;

class RecipeDataTableConfigurator implements DataTableConfiguratorInterface
{
    public function __construct(
        private readonly DataTableFactory $dataTableFactory
    ) {
    }

    public function supports(string $entityClass): bool
    {
        return $entityClass === Recipe::class;
    }

    public function configure(): void
    {
        $builder = new DataTableBuilder('admin_recipe', Recipe::class);

        $builder
            ->addField('title', 'string', [
                'label' => 'Titre',
                'sortable' => true,
            ])
            ->addField('servings', 'string', [
                'label' => 'Portions',
                'sortable' => true,
            ])
            ->addField('prepMinutes', 'string', [
                'label' => 'Préparation (min)',
                'sortable' => true,
            ])
            ->addField('cookMinutes', 'string', [
                'label' => 'Cuisson (min)',
                'sortable' => true,
            ])
            ->addField('createdAt', 'datetime', [
                'label' => 'Créé le',
                'sortable' => true,
                'format' => 'd/m/Y',
            ])
            
            ->addFilter('title', 'string', [
                'label' => 'Titre',
            ])
            ->addAction('show', [
                'label' => 'Voir',
                'route' => 'app_recipe_show',
                'route_params' => ['id' => '{id}'],
                'css_class' => 'btn btn-sm btn-primary',
            ])
            ->addAction('edit', [
                'label' => 'Modifier',
                'route' => 'admin_recipe_edit',
                'route_params' => ['id' => '{id}'],
                'css_class' => 'btn btn-sm btn-warning',
            ])
            ->addAction('delete', [
                'label' => 'Supprimer',
                'route' => 'admin_recipe_delete',
                'route_params' => ['id' => '{id}'],
                'css_class' => 'btn btn-sm btn-danger',
                'attributes' => [
                    'onclick' => "return confirm('Êtes-vous sûr de vouloir supprimer cette recette ?')",
                ],
            ]);

        $this->dataTableFactory->getDataTableRegistry()->register($builder->getConfig());
    }
}

