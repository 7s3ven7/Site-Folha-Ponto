<?php

namespace App\Entity;

use App\Service\Manipulator\TransformObject;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Excel
{

    private string $fileName;
    private string $fullPatch;
    public array $classStyle;
    public array $positionClassStyle;
    private Spreadsheet $file;
    private Xlsx $saver;

    public function __construct()
    {
        $this->fileName = '';
        $this->fullPatch = '';
        $this->classStyle = [];
        $this->positionClassStyle = [];
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

        if (isset($data->variables) && is_object($data->variables)) {
            $TransformObject = new TransformObject();

            $classArray = $TransformObject->returnArray($data->variables);
            $this->StyleSeparator($classArray);
        }

        if (isset($data->cell)) {
            $this->writewithArray($data->cell, $spreadsheet);
        }


        if ($this->classStyle !== []) {
            $this->setStyle($spreadsheet);
        }

    }

    private function StyleSeparator(array $classes): void
    {

        $mapArray = [
            'background' => 'fill',
            'backgroundVars' => [
                'backgroundType' => 'fillType',
                'backgroundColor' => 'startColor',
                'backgroundColorVars' => [
                    'rgb' => 'rgb'
                ]
            ],
            'font' => 'font',
            'fontVars' => [
                'fontWeight' => 'bold',
                'fontSize' => 'size',
                'color' => 'color',
                'colorVars' => [
                    'rgb' => 'rgb'
                ]
            ],
            'alignmentText' => 'alignment',
            'alignmentTextVars' => [
                'horizontal' => 'horizontal',
                'vertical' => 'vertical',
            ],
            'border' => 'borders',
            'borderVars' => [
                'all' => 'allBorders',
                'allVars' => [
                    'style' => 'borderStyle',
                    'color' => 'color',
                    'colorVars' => [
                        'rgb' => 'rgb'
                    ]
                ],
                'outLineVars' => [
                    'outline' => 'outline',
                ]
            ],
            'numberFormat' => 'numberFormat',
            'numberFormatVars' => [
                'format' => 'formatCode'
            ],
            'outLine' => 'outline'
        ];

        foreach ($classes as $name => $class) {
            $this->classStyle[$name] = [];
            $this->filterStyle($this->classStyle[$name], $class, $mapArray);
        };

    }

    private function filterStyle(array &$path, array $class, array $map): void
    {

        $mapValues = ['backgroundType' => [
            'solid' => fill::FILL_SOLID
        ]];

        foreach ($class as $key => $value) {
            if (isset($map[$key])) {

                $path[$map[$key]] = [];

                if (is_array($value) && is_array($map[$key . 'Vars'])) {
                    $this->filterStyle($path[$map[$key]], $value, $map[$key . 'Vars']);
                }

                if (!is_array($value)) {
                    if (isset($mapValues[$key])) {
                        foreach ($mapValues[$key] as $keyValues => $constantValues) {
                            if ($keyValues === $value) {
                                $path[$map[$key]] = $constantValues;
                            }
                        };
                    } else {
                        $path[$map[$key]] = $value;
                    }
                }

            }

        }

    }

    private function setStyle(Worksheet $spreadsheet): void
    {
        foreach ($this->classStyle as $name => $class) {
            foreach ($this->positionClassStyle[$name] as $position) {
                $spreadsheet->applyStylesFromArray($position, $class);
            }
        }
    }

    private function writeWithArray(array $cells, Worksheet $spreadsheet): void
    {
        foreach ($cells as $cell) {
            $spreadsheet->setCellValue($cell->position, $cell->value);
            $this->positionClassStyle[$cell->style][] = $cell->position;
        }
    }


}