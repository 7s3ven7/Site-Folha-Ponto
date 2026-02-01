<?php

namespace Src;

class Csv
{

    private string $filename;
    private array $path = [];
    private string $fullPath;


    public function __construct(string $filename, array $path = [])
    {

        $this->filename = $filename;

        $this->path = $path;

    }

    public function writeCSV(array $data, bool $read): mixed
    {

        if ($read) {
            $file = $this->readCSV($this->filename);
        } else {
            $file = $this->createCSV($data);
        }

        return $file;

    }

    private function testPath(): string
    {

        $fullPath = '';

        foreach ($this->path as $path) {
            $fullPath .= $path;

            if (!is_dir($fullPath)) {
                mkdir($fullPath);
            }

            $fullPath .= '\\';

        }
        $this->fullPath = $fullPath;

        return $fullPath;

    }

    private function createCSV(array $data): mixed
    {

        $fullPath = $this->testPath();

        //numero incremental para não tentar criar arquivo existente, e modificar filename caso precise
        $i = 1;
        while (file_exists($fullPath . $this->filename . '(' . $i . ').csv')) {
            $i++;
        }

        return fopen($fullPath . $this->filename . '(' . $i . ').csv', 'w+');

    }

    private function readCSV(): mixed
    {

        $fullPath = $this->testPath();

        if (file_exists($fullPath . $this->filename . '.csv')) {
            return $this->transformCSV(fopen($fullPath . $this->filename . '.csv', 'r'));
        }

        return ['error' => 'file not found'];

    }

    private function transformCSV(mixed $file): mixed
    {

        //$map = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm','n','o','p','q','r','s', 't', 'u', 'v', 'w', 'x', 'y', 'z'];

        $lines = [];

        while (($line = fgetcsv($file)) !== false) {
            $lines[] = $line;

        }

        return $lines;
    }

}