<?php

namespace App\Controller\Admin;

use App\DataTable\DataTableFactory;
use App\DataTable\DataTableConfigLoader;
use App\DataTable\Service\ExcelExporter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class DataTableExportController extends AbstractController
{
    public function __construct(
        private readonly DataTableFactory $dataTableFactory,
        private readonly DataTableConfigLoader $configLoader,
        private readonly ExcelExporter $excelExporter
    ) {
    }

    #[Route('/admin/datatable/export', name: 'admin_datatable_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        $entityClass = $request->query->get('entity_class');
        $sortColumn = $request->query->get('sort_column', 'id');
        $sortDirection = $request->query->get('sort_direction', 'ASC');
        
        if ($entityClass === null) {
            throw new \InvalidArgumentException('entity_class parameter is required');
        }

        $this->configLoader->loadForEntity($entityClass);
        $config = $this->dataTableFactory->getDataTableRegistry()->getByEntityClass($entityClass);
        
        if ($config === null) {
            throw new \RuntimeException(sprintf('No configuration found for entity class: %s', $entityClass));
        }

        $filters = [];
        $filterData = $request->query->get('filters', '{}');
        if (is_string($filterData)) {
            $filters = json_decode($filterData, true) ?? [];
        } elseif (is_array($filterData)) {
            $filters = $filterData;
        }

        $visibleColumns = [];
        $columnsData = $request->query->get('columns', '{}');
        if (is_string($columnsData)) {
            $visibleColumns = json_decode($columnsData, true) ?? [];
        } elseif (is_array($columnsData)) {
            $visibleColumns = $columnsData;
        }

        $parameters = [
            'sort_column' => $sortColumn,
            'sort_direction' => $sortDirection,
            'filters' => $filters,
        ];

        return $this->excelExporter->export(
            $config,
            $parameters,
            $visibleColumns,
            $entityClass
        );
    }
}

