<?php

namespace App\Entity;

class Csv
{

    private string $filename;
    private array $path = [];
    private string $fullPath = "";


    public function __construct(string $filename, array $path = [])
    {

        $this->filename = $filename;

        $this->path = $path;

    }

    public function writeCSV(array $data, bool $read): array
    {

        $this->testPath();

        if ($read) {
            $file = fopen($this->fullPath . $this->filename, 'a');
        } else {
            $file = $this->createCSV();
        }

        if (is_array($file) && isset($file['error'])) {
            return $file;
        }

        var_dump($file);

        foreach ($data as $row) {
            $lineAsWrite = '';
            foreach ($row as $value) {
                $lineAsWrite .= $value . ',';
            }
            $lineAsWrite = substr($lineAsWrite, 0, -1);
            $lineAsWrite .= "\n";
            $error = fwrite($file, $lineAsWrite);
            if (is_bool($error) && !$error) {
                return ['success' => false, 'error' => 'Error writing file', 'path' => $this->fullPath, 'line' => $lineAsWrite];
            }
        }

        return ['success' => true];

    }

    private function testPath(): void
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

    }

    private function createCSV(): mixed
    {

        if (is_file($this->fullPath . $this->filename)) {
            return ['error' => 'File already exists ' . $this->fullPath . $this->filename];
        }

        return fopen($this->fullPath . $this->filename, 'w+');

    }

    public function GetContentCSV(): mixed
    {

        if (file_exists($this->fullPath . $this->filename)) {
            return $this->transformCSV(fopen($this->fullPath . $this->filename, 'r'));
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

    public function deleteCSV(): array
    {
        $this->testPath();

        if (is_file($this->fullPath . $this->filename)) {

            if (!unlink($this->fullPath . $this->filename)) {
                return ['error' => 'Error deleting file'];
            }

        } else {
            return ['error' => 'file not found'];
        }

        return ['success' => true];
    }

}