<?php

namespace App\DataTable\Configurator;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Interface pour les configurators de DataTable.
 */
#[AutoconfigureTag('datatable.configurator')]
interface DataTableConfiguratorInterface
{
    /**
     * Vérifie si ce configurateur supporte l'entité donnée.
     */
    public function supports(string $entityClass): bool;

    /**
     * Configure le DataTable pour l'entité supportée.
     */
    public function configure(): void;
}

