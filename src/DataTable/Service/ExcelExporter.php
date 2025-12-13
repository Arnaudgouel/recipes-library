<?php

namespace App\DataTable\Service;

use App\DataTable\Config\DataTableConfig;
use App\DataTable\DataProvider\DataProviderInterface;
use App\DataTable\FieldRenderer;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Service pour exporter les données du DataTable vers Excel.
 */
class ExcelExporter
{
    public function __construct(
        private readonly DataProviderInterface $dataProvider,
        private readonly FieldRenderer $fieldRenderer
    ) {
    }

    public function export(
        DataTableConfig $config,
        array $parameters,
        array $visibleColumns,
        string $entityClass
    ): StreamedResponse {
        $allData = $this->dataProvider->getAllData($config, $parameters);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [];
        $columnIndex = 1;
        foreach ($visibleColumns as $columnName => $columnData) {
            if ($columnData['visible'] ?? true) {
                $headers[$columnName] = $columnIndex;
                $columnLetter = Coordinate::stringFromColumnIndex($columnIndex);
                $sheet->setCellValue($columnLetter . '1', $columnData['label'] ?? $columnName);
                $columnIndex++;
            }
        }

        $lastColumnLetter = Coordinate::stringFromColumnIndex($columnIndex - 1);
        $headerRange = 'A1:' . $lastColumnLetter . '1';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $rowIndex = 2;
        foreach ($allData as $entity) {
            foreach ($headers as $fieldName => $headerColIndex) {
                $fieldConfig = $config->getField($fieldName);
                
                if ($fieldConfig === null) {
                    $cleanValue = '';
                } else {
                    $renderedValue = $this->fieldRenderer->render($entity, $fieldName, $fieldConfig);
                    $cleanValue = strip_tags($renderedValue);
                    $cleanValue = html_entity_decode($cleanValue, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                }
                
                $columnLetter = Coordinate::stringFromColumnIndex($headerColIndex);
                $sheet->setCellValue($columnLetter . $rowIndex, $cleanValue);
            }
            $rowIndex++;
        }

        foreach ($headers as $colIndex) {
            $columnLetter = Coordinate::stringFromColumnIndex($colIndex);
            $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        $entityName = basename(str_replace('\\', '/', $entityClass));
        $filename = sprintf(
            'export_%s_%s.xlsx',
            strtolower($entityName),
            date('Y-m-d_His')
        );

        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $filename));
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
}

