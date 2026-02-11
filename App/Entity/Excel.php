<?php

namespace App\Entity;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Excel
{

    private string $fileName;
    private string $fullPatch;
    public array $classStyle;
    private Spreadsheet $file;
    private Xlsx $saver;

    public function __construct()
    {
        $this->fileName = '';
        $this->fullPatch = '';
        $this->classStyle = [];
        $this->file = new Spreadsheet();
        $this->file->setActiveSheetIndex(0);
    }

    public function setMetaData(string $fileName, array $path = []): array
    {

        $this->fileName = $fileName;
        $path = $this->resolvePatch($path);

        if (is_array($path)) {
            return $path;
        }

        return ['success' => true];
    }

    public function saveFile(): array
    {

        $this->saver = new Xlsx($this->file);
        $this->saver->save($this->fullPatch . $this->fileName);

        return ['success' => true];
    }

    private function resolvePatch(array $path): string|array
    {
        $fullPatch = '';

        if ($path === []) {
            return '';
        }

        foreach ($path as $value) {
            $fullPatch .= $value . '\\';

            if (!is_dir($fullPatch)) {
                mkdir($fullPatch);
            }

        }

        if (is_dir($fullPatch)) {
            $this->fullPatch = $fullPatch;
            return $fullPatch;
        }

        return ['success' => false, 'error' => 'Not possible resolve patch'];
    }

    public function getFile(): void
    {
        $this->saver = new Xlsx($this->file);
        $this->saver->save('php://output');
    }

    public function writeData(object $data): void
    {

        $spreadsheet = $this->file->getActiveSheet();

        if (isset($data->cell)) {
            $this->writewithArray($data, $spreadsheet);
        }

        var_dump($this->classStyle);

    }

    private function writeWithArray(object $data, Worksheet $spreadsheet): void
    {

        foreach ($data->cell as $value) {

            if (isset($value->style)) {

                if (!isset($this->classStyle[$value->style])) {

                    if (isset($data->variables->{$value->style})) {

                        $this->filterStyle($data->variables->{$value->style}, $value->style);

                    }

                }
            }

            $spreadsheet->setCellValue($value->position, $value->value);

        }
    }

    private function filterStyle(object $cell, string $class): void
    {

        $map = [
            'font' => [
                'name' => '',
                'size' => '',
                'bold' => '',
                'italic' => '',
                'underline' => '',
                'color' => ''
            ],
            'background' => [
                'backgroundType' => '',
                'backgroundColor' => ''
            ],
            'alignment' => [
                'horizontal' => '',
                'vertical' => '',
            ],
            'border' => [
                'all' => [
                    'borderStyle' => '',
                    'color' => ''
                ]
            ]
        ];

        $this->classStyle[$class] = [];
        $this->verifyStyle($cell, $map, $this->classStyle[$class]);

    }

    private function verifyStyle(object $cell, array $map, array &$path = []): void
    {

        foreach ($map as $field => $var) {

            if (isset($cell->{$field})) {

                $newPath = $this->filterNameFields($field, $cell, $path);

                if (is_array($var)) {


                    if (is_object($cell->{$field})) {
                        $this->verifyStyle($cell->{$field}, $var, $newPath);
                    }

                }
            }

        }

    }

    private function filterNameFields(string|int|bool $fieldCell, object $cell, array &$path): array
    {

        $map = [
            'background' => 'fill',
            'backgroundType' => 'fillType',
            'backgroundColor' => 'starterColor',];

        foreach ($map as $field => $var) {

            if ($field === $fieldCell) {

                $path[$var] = [];
                var_dump($path);

                if (is_object($cell->{$var})) {
                    $this->filterNameFields($field, $cell, $path);
                }

                $this->filterNameFieldAsConst($var, $cell->{$field}, $path[$var]);

            }

            break;
        }

        return $path;
    }

    private
    function filterNameFieldAsConst(string|int|bool $fieldCell, object $cell, array &$path): void
    {

        $map = ['backgroundType' => 'fillType',
            [
                'solid' => Fill::FILL_SOLID,
            ],
        ];
        foreach ($map as $field => $typeValues) {
            if ($field === $fieldCell) {
                $path[$typeValues] = [];
                foreach ($typeValues as $value => $const) {
                    echo PHP_EOL;
                    echo PHP_EOL;
                    if ($cell->{$field} === $value) {
                        $path[$field] = $const;
                    }
                }
            }
        }

    }


}