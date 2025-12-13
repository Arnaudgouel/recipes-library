<?php

namespace App\Twig\Components;

use App\DataTable\Config\ActionConfig;
use App\DataTable\Config\DataTableConfig;
use App\DataTable\DataTableConfigLoader;
use App\DataTable\DataTableFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\RouterInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Twig\Environment;

#[AsLiveComponent]
class DataTable
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public ?string $entityClass = null;

    #[LiveProp(writable: true)]
    public array $allColumns = [];

    #[LiveProp]
    public string $sortColumn = 'id';

    #[LiveProp]
    public string $sortDirection = 'ASC';

    #[LiveProp(writable: true)]
    public array $filters = [];

    #[LiveProp]
    public int $page = 1;

    #[LiveProp]
    public int $itemsPerPage;

    #[LiveProp]
    public ?string $routePrefix = null;

    #[LiveProp]
    public bool $exportable;

    private ?DataTableConfig $config = null;

    public function __construct(
        private readonly DataTableFactory $dataTableFactory,
        private readonly DataTableConfigLoader $configLoader,
        private readonly Environment $twig,
        private readonly EntityManagerInterface $entityManager,
        private readonly RouterInterface $router,
        private readonly Security $security
    ) {
    }

    public function mount(
        ?string $entityClass = null,
        ?string $routePrefix = null,
        int $itemsPerPage = 25,
        string $sortColumn = 'id',
        string $sortDirection = 'ASC',
        bool $exportable = true
    ): void {
        if ($entityClass !== null) {
            $this->entityClass = $entityClass;
        }
        
        if ($routePrefix !== null) {
            $this->routePrefix = $routePrefix;
        }
        
        $this->itemsPerPage = $itemsPerPage;
        $this->sortColumn = $sortColumn;
        $this->sortDirection = $sortDirection;
        $this->exportable = $exportable;

        if ($this->entityClass === null) {
            throw new \RuntimeException('entityClass must be provided to DataTable component');
        }
        
        $this->configLoader->loadForEntity($this->entityClass);
        
        $this->config = $this->dataTableFactory->getDataTableRegistry()->getByEntityClass($this->entityClass)
            ?? $this->dataTableFactory->getOrCreateConfig($this->entityClass);
        
        if (empty($this->allColumns)) {
            foreach ($this->config->getFields() as $field) {
                $isAccessible = $this->isFieldAccessible($field);
                
                $this->allColumns[$field->name] = [
                    'name' => $field->name,
                    'label' => $field->getLabel() ?? $field->name,
                    'type' => $field->type,
                    'sortable' => $field->isSortable() ?? false,
                    'visible' => $isAccessible && ($field->isVisible() ?? true),
                ];
            }
        }

        if (empty($this->filters)) {
            $this->initializeFilters();
        }
    }

    #[LiveAction]
    public function toggleColumn(#[LiveArg] string $column): void
    {
        if (isset($this->allColumns[$column])) {
            $this->allColumns[$column]['visible'] = !$this->allColumns[$column]['visible'];
        }
    }

    #[LiveAction]
    public function sort(#[LiveArg] string $column): void
    {
        if ($this->sortColumn === $column) {
            if ($this->sortDirection === 'ASC') {
                $this->sortDirection = 'DESC';
            } elseif ($this->sortDirection === 'DESC') {
                $this->sortDirection = 'ASC';
                $this->sortColumn = 'id';
            }
        } else {
            $this->sortColumn = $column;
            $this->sortDirection = 'ASC';
        }
    }
    
    #[LiveAction]
    public function setPage(#[LiveArg] int $page): void
    {
        $this->page = $page;
    }

    #[LiveAction]
    public function setItemsPerPage(#[LiveArg] int $itemsPerPage): void
    {
        $this->itemsPerPage = $itemsPerPage;
        $this->page = 1;
    }

    #[LiveAction]
    public function clearFilters(): void
    {
        foreach ($this->filters as $filter => $value) {
            if (is_array($value)) {
                foreach ($value as $key => $v) {
                    $this->filters[$filter][$key] = '';
                }
            } else {
                $this->filters[$filter] = '';
            }
        }
        $this->page = 1;
        $this->dispatchBrowserEvent('clearFilters');
    }

    #[LiveListener('clearONEFILTER')]
    public function clearONEFILTER(#[LiveArg] string $filterName): void
    {
        if (is_array($this->filters[$filterName])) {
            foreach ($this->filters[$filterName] as $key => $value) {
                $this->filters[$filterName][$key] = '';
            }
        } else {
            $this->filters[$filterName] = '';
        }
    }

    public function getItems(): array
    {
        $config = $this->getConfig();
        $dataProvider = $this->dataTableFactory->getDataProvider();
        
        return $dataProvider->getData($config, [
            'page' => $this->page,
            'items_per_page' => $this->itemsPerPage,
            'sort_column' => $this->sortColumn,
            'sort_direction' => $this->sortDirection,
            'filters' => $this->filters,
        ]);
    }

    public function getTotalItems(): int
    {
        $config = $this->getConfig();
        $dataProvider = $this->dataTableFactory->getDataProvider();
        
        return $dataProvider->getCount($config, [
            'filters' => $this->filters,
        ]);
    }

    public function getTotalPages(): int
    {
        return (int) ceil($this->getTotalItems() / $this->itemsPerPage);
    }

    public function renderField(mixed $entity, string $fieldName): string
    {
        if ($this->entityClass === null) {
            throw new \RuntimeException('entityClass must be provided to DataTable component');
        }
        
        $config = $this->getConfig();
        $fieldConfig = $config->getField($fieldName);
        $value = $this->getFieldValue($entity, $fieldName);
        
        if ($fieldConfig === null) {
            throw new \RuntimeException(sprintf('Le champ "%s" doit être explicitement configuré avec addField().', $fieldName));
        }
        
        $fieldType = $fieldConfig->type;
        
        if ($fieldConfig->getTemplate() !== null) {
            try {
                return $this->twig->render($fieldConfig->getTemplate(), [
                    'entity' => $entity,
                    'value' => $value,
                    'fieldName' => $fieldName,
                    'fieldConfig' => $fieldConfig,
                    'options' => $fieldConfig->options,
                ]);
            } catch (\Exception $e) {
            }
        }
        
        $defaultTemplate = 'datatable/field/' . $fieldType . '.html.twig';
        try {
            $loader = $this->twig->getLoader();
            if ($loader->exists($defaultTemplate)) {
                return $this->twig->render($defaultTemplate, [
                    'entity' => $entity,
                    'value' => $value,
                    'fieldName' => $fieldName,
                    'fieldConfig' => $fieldConfig,
                    'options' => $fieldConfig->options ?? [],
                ]);
            }
        } catch (\Exception $e) {
        }
        
        $fieldRenderer = $this->dataTableFactory->getFieldRenderer();
        return $fieldRenderer->render($entity, $fieldName, $fieldConfig);
    }

    private function getFieldValue(mixed $entity, string $fieldName): mixed
    {
        $propertyAccessor = $this->dataTableFactory->getPropertyAccessor();
        try {
            return $propertyAccessor->getValue($entity, $fieldName);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getEntityOptions(string $filterName): array
    {
        $config = $this->getConfig();
        $filterConfig = $config->getFilter($filterName);
        
        if ($filterConfig === null) {
            return [];
        }

        $options = $filterConfig->options;
        $targetEntity = $options['target_entity'] ?? null;
        
        if ($targetEntity === null) {
            return [];
        }

        try {
            $repository = $this->entityManager->getRepository($targetEntity);
            $entities = $repository->findAll();
            
            $labelField = $options['label_field'] ?? 'nom';
            $result = [];
            
            foreach ($entities as $entity) {
                $id = method_exists($entity, 'getId') ? $entity->getId() : null;
                if ($id === null) {
                    continue;
                }
                
                $label = null;
                if (method_exists($entity, 'get' . ucfirst($labelField))) {
                    $getter = 'get' . ucfirst($labelField);
                    $label = $entity->$getter();
                } elseif (method_exists($entity, '__toString')) {
                    $label = (string) $entity;
                } else {
                    $label = '#' . $id;
                }
                
                $result[] = [
                    'id' => $id,
                    'label' => $label,
                ];
            }
            
            return $result;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getFilterTargetEntity(string $filterName): ?string
    {
        $config = $this->getConfig();
        $filterConfig = $config->getFilter($filterName);
        
        if ($filterConfig === null) {
            return null;
        }

        return $filterConfig->options['target_entity'] ?? null;
    }

    public function getFilterLabelField(string $filterName): string
    {
        $config = $this->getConfig();
        $filterConfig = $config->getFilter($filterName);
        
        if ($filterConfig === null) {
            return 'nom';
        }

        return $filterConfig->options['label_field'] ?? 'nom';
    }

    public function getFilterAutocompleteRoute(string $filterName): array
    {
        $config = $this->getConfig();
        $filterConfig = $config->getFilter($filterName);
        
        if ($filterConfig === null) {
            throw new \App\DataTable\Exception\MissingAutocompleteRouteException($filterName, 'unknown');
        }

        $options = $filterConfig->options;
        
        if (!($options['autocomplete'] ?? false)) {
            throw new \RuntimeException(sprintf('Le filtre "%s" n\'a pas l\'autocomplete activé.', $filterName));
        }

        if (!isset($options['route']) && !isset($options['alias'])) {
            throw new \App\DataTable\Exception\MissingAutocompleteRouteException($filterName, 'unknown');
        }

        return [
            'route' => $options['route'] ?? 'ux_entity_autocomplete',
            'alias' => $options['alias'] ?? null,
        ];
    }

    public function getConfiguredActions(): array
    {
        $config = $this->getConfig();
        return $config->getActions();
    }

    public function hasActions(): bool
    {
        $config = $this->getConfig();
        return !empty($config->getActions());
    }

    public function renderAction(mixed $entity, string $actionName): string
    {
        $config = $this->getConfig();
        $actionConfig = $config->getAction($actionName);
        
        if ($actionConfig === null) {
            return '';
        }

        if (!$actionConfig->isVisible($entity)) {
            return '';
        }

        if ($actionConfig->getTemplate() !== null) {
            try {
                $url = $this->getActionUrl($entity, $actionConfig);
                return $this->twig->render($actionConfig->getTemplate(), [
                    'entity' => $entity,
                    'action' => $actionConfig,
                    'options' => $actionConfig->options,
                    'url' => $url,
                    'routePrefix' => $this->getRoutePrefix(),
                ]);
            } catch (\Exception $e) {
            }
        }

        $url = $this->getActionUrl($entity, $actionConfig);
        $label = $actionConfig->getLabel();
        $cssClass = $actionConfig->getCssClass();
        $attributes = $actionConfig->getAttributes();
        
        $attributesString = '';
        foreach ($attributes as $key => $value) {
            $attributesString .= sprintf(' %s="%s"', htmlspecialchars($key), htmlspecialchars($value));
        }

        return sprintf(
            '<a href="%s" class="%s"%s>%s</a>',
            htmlspecialchars($url),
            htmlspecialchars($cssClass),
            $attributesString,
            htmlspecialchars($label)
        );
    }

    private function getActionUrl(mixed $entity, ActionConfig $actionConfig): string
    {
        if ($actionConfig->getUrl() !== null) {
            return $actionConfig->getUrl();
        }

        if ($actionConfig->getRoute() !== null) {
            $routeParams = $actionConfig->getRouteParams();
            
            foreach ($routeParams as $key => $value) {
                if (is_string($value) && str_starts_with($value, '{')) {
                    $fieldName = trim($value, '{}');
                    $propertyAccessor = $this->dataTableFactory->getPropertyAccessor();
                    try {
                        $routeParams[$key] = $propertyAccessor->getValue($entity, $fieldName);
                    } catch (\Exception $e) {
                        $routeParams[$key] = method_exists($entity, 'getId') ? $entity->getId() : null;
                    }
                }
            }

            if (empty($routeParams)) {
                $id = method_exists($entity, 'getId') ? $entity->getId() : null;
                if ($id !== null) {
                    $routeParams['id'] = $id;
                }
            }

            return $this->router->generate($actionConfig->getRoute(), $routeParams);
        }

        $routePrefix = $this->getRoutePrefix();
        $id = method_exists($entity, 'getId') ? $entity->getId() : null;
        
        if ($id !== null) {
            return $this->router->generate($routePrefix . '_' . $actionConfig->name, ['id' => $id]);
        }

        return '#';
    }

    public function getAllColumns(): array
    {
        $result = [];
        $config = $this->getConfig();
        
        foreach ($this->allColumns as $column => $fieldConfig) {
            $field = $config->getField($column);
            if ($field !== null && !$this->isFieldAccessible($field)) {
                continue;
            }
            $result[$column] = $fieldConfig;
        }
        
        return $result;
    }

    public function getVisibleColumns(): array
    {
        $result = [];
        $config = $this->getConfig();
        
        foreach ($this->allColumns as $column => $fieldConfig) {
            if (!$fieldConfig['visible']) {
                continue;
            }
            
            $field = $config->getField($column);
            if ($field !== null && !$this->isFieldAccessible($field)) {
                continue;
            }
            
            $result[$column] = $fieldConfig;
        }
        return $result;    
    }

    private function isFieldAccessible(\App\DataTable\Config\FieldConfig $field): bool
    {
        $requiredRole = $field->getRequiredRole();
        
        if ($requiredRole === null) {
            return true;
        }
        
        return $this->security->isGranted($requiredRole);
    }

    public function getConfiguredFilters(): array
    {
        $config = $this->getConfig();
        return $config->getFilters();
    }

    public function getActiveFiltersCount(): int
    {
        $count = 0;
        foreach ($this->filters as $value) {
            if (is_array($value)) {
                foreach ($value as $v) {
                    if (!empty($v)) {
                        $count++;
                        break;
                    }
                }
            } elseif (!empty($value)) {
                $count++;
            }
        }
        return $count;
    }

    public function getConfig(): DataTableConfig
    {
        if ($this->config === null) {
            if ($this->entityClass === null) {
                throw new \RuntimeException('entityClass must be provided to DataTable component');
            }
            
            $this->config = $this->dataTableFactory->getDataTableRegistry()->getByEntityClass($this->entityClass)
                ?? $this->dataTableFactory->getOrCreateConfig($this->entityClass);
        }
        
        return $this->config;
    }

    public function getRoutePrefix(): string
    {
        if ($this->routePrefix !== null) {
            return $this->routePrefix;
        }
        
        if ($this->entityClass === null) {
            throw new \RuntimeException('entityClass must be provided to DataTable component');
        }
        
        $config = $this->getConfig();
        return $config->name;
    }

    public function getItemsData(): array
    {
        return $this->getItems();
    }

    public function getTotalItemsCount(): int
    {
        return $this->getTotalItems();
    }

    public function getTotalPagesCount(): int
    {
        return $this->getTotalPages();
    }

    public function getRoutePrefixValue(): string
    {
        return $this->getRoutePrefix();
    }

    private function initializeFilters(): void
    {
        $config = $this->getConfig();
        $filters = $config->getFilters();
        
        if (!empty($filters)) {
            foreach ($filters as $filter) {
                if ($filter->type === 'date_range') {
                    $this->filters[$filter->name] = [
                        'from' => '',
                        'to' => '',
                    ];
                } else {
                    $this->filters[$filter->name] = '';
                }
            }
        } else {
            $this->filters = array_fill_keys(array_keys($this->allColumns), '');
        }
    }
}

