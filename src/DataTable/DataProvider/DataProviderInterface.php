<?php

namespace App\DataTable\DataProvider;

use App\DataTable\Config\DataTableConfig;

/**
 * Interface pour les fournisseurs de données du DataTable.
 */
interface DataProviderInterface
{
    /**
     * Récupère les données paginées selon la configuration.
     */
    public function getData(DataTableConfig $config, array $parameters): array;

    /**
     * Récupère le nombre total d'éléments selon les filtres.
     */
    public function getCount(DataTableConfig $config, array $parameters): int;

    /**
     * Récupère toutes les données sans pagination (pour l'export).
     */
    public function getAllData(DataTableConfig $config, array $parameters): array;
}

