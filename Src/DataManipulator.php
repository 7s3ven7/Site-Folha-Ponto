<?php

namespace Src;

use Exception;
use stdClass;

class DataManipulator
{

    private array $response = [];

    private array $variablesTypes;

    public function getResponse(): array|null
    {

        if (count($this->response) > 0) {

            return $this->response;
        }

        return null;
    }

    private function filter(string $data): array|null
    {

        $dataFormatArray = json_decode($data, true);

        if (Json_last_error() !== JSON_ERROR_NONE) {
            $this->response = ['error' => ['message' => 'Json incorrect']];
            return null;
        }

        foreach ($dataFormatArray as $key => $value) {
            $patch[] = $key;
            $this->validate($value, $key, $patch);
        }


        return $dataFormatArray;

    }

    private function validate(mixed $data, string $lastKey, array $patch): void
    {

        if (is_array($data) && (count($data) > 0) && $lastKey != 'value') {

            foreach ($data as $key => $value) {

                $newPatch = $patch;
                $newPatch[] = $key;
                $this->validate($value, $key, $newPatch);

            }

        }

        if ($lastKey === 'type') {
            $this->variablesTypes[$patch[count($patch) - 2]][$lastKey] = $data;
            return;
        }
        if ($lastKey === 'value') {

            if ($data === "") {
                $this->variablesTypes[$patch[count($patch) - 2]][$lastKey] = null;
                return;
            }

            switch ($this->variablesTypes[$patch[count($patch) - 2]]['type']) {
                case 'int':

                    if (!is_numeric($data)) {
                        $this->response = ['error' => ['message' => 'Data must be a int', 'Key' => $lastKey, 'Data' => $data, 'Patch' => $patch]];
                        return;
                    }

                    try {
                        for ($i = 0; $i < strlen($data);) {
                            if ($data[$i] === '0') {
                                $data = substr_replace($data, '', $i, 1);
                            } else {
                                $i++;
                            }
                        }

                        if ($data != floor($data)){
                            $this->response = ['error' => ['message' => 'Data must be a int not float', 'Key' => $lastKey, 'Data' => $data, 'Patch' => $patch]];
                        }

                            $data = (int)$data;

                    } catch (Exception) {
                        $this->response = ['error' => ['message' => 'Data must be a int', 'Key' => $lastKey, 'Data' => $data, 'Patch' => $patch]];
                    }


                    if (($data >= 0) && (!ctype_digit((string)$data))) {
                        $this->response = ['error' => ['message' => 'Data must be a int not float', 'Key' => $lastKey, 'Data' => $data, 'Patch' => $patch]];
                        return;
                    }

                    $this->variablesTypes[$patch[count($patch) - 2]][$lastKey] = (int)$data;

                    return;
                case 'float':

                    $data = str_replace(',', '.', $data);

                    if (!is_numeric($data)) {
                        $this->response = ['error' => ['message' => 'Data must be a float', 'Key' => $lastKey, 'Data' => $data, 'Patch' => $patch]];
                        return;
                    }

                    $this->variablesTypes[$patch[count($patch) - 2]][$lastKey] = (float)$data;

                    return;
                case 'bool':

                    $bool = filter_var($data, FILTER_VALIDATE_BOOLEAN);

                    if (!is_bool($bool)) {
                        $this->response = ['error' => ['message' => 'Data must be a boolean', 'Key' => $lastKey, 'Data' => $data, 'Patch' => $patch]];
                        return;
                    }

                    $this->variablesTypes[$patch[count($patch) - 2]][$lastKey] = $bool;

                    return;
                case 'string':

                    try {
                        if (is_array($data)) {
                            if (count($data) === 1) {
                                $data = $data[0];

                            } else {
                                $this->response = ['error' => ['message' => 'Data array have must one index', 'Key' => $lastKey, 'Data' => $data, 'Patch' => $patch]];
                            }
                        }
                        $data = (string)$data;
                    } catch (Exception $e) {
                        $this->response = ['error' => ['message' => 'Data must be a string', 'Key' => $lastKey, 'Data' => $data, 'Patch' => $patch]];
                        return;
                    }

                    $this->variablesTypes[$patch[count($patch) - 2]][$lastKey] = (string)$data;

                    return;
                case 'array':

                    if (is_string($data)) {
                        $data = explode(',', $data);
                    }

                    if (!is_array($data)) {
                        $this->response = ['error' => ['message' => 'Data must be an array', 'Key' => $lastKey, 'Data' => $data, 'Patch' => $patch]];
                        return;
                    }

                    $this->variablesTypes[$patch[count($patch) - 2]][$lastKey] = $data;

                    return;
                case 'object':

                    $dataManipulator = new DataManipulator();

                    $object = $dataManipulator->createObject(json_encode($data));

                    if (is_array($object)) {
                        $this->response = $object;
                        break;
                    }

                    $this->variablesTypes[$patch[count($patch) - 2]][$lastKey] = $object;

                    return;
            }
        }
    }

    public function createObject(string $data): object|array
    {
        $data = $this->filter($data);

        if (count($this->response) > 0) {
            return $this->response;
        }

        $object = new class() {
            public array $variablesTypes = [];

            public function setter(string $key, mixed $value): bool
            {

                switch ($this->variablesTypes[$key]['type']) {
                    case
                    'int':

                        if (!is_numeric($value)) {
                            return false;
                        }

                        if (!ctype_digit($value)) {
                            return false;
                        }

                        $this->{$key} = (int)$value;

                        return true;
                    case 'float':

                        if (!is_numeric($value)) {
                            return false;
                        }

                        $this->{$key} = (float)$value;

                        return true;
                    case 'bool':

                        $bool = filter_var($value, FILTER_VALIDATE_BOOLEAN);

                        if (!is_bool($bool)) {
                            return false;
                        }

                        $this->{$key} = $value;

                        return true;
                    case 'string':

                        if (!is_string($value)) {
                            return false;
                        }

                        $this->{$key} = $value;

                        return true;
                    case 'array':

                        if (!is_array($value)) {
                            return false;
                        }

                        $this->{$key} = $value;

                        return true;
                    case 'object':

                        if (!is_object($value)) {
                            return false;
                        }

                        $this->{$key} = $value;

                        return true;
                }

                return false;
            }
        };

        $object->variablesTypes = $this->variablesTypes;

        foreach ($this->variablesTypes as $key => $value) {

            $object->{$key} = $value['value'];

        }

        return $object;
    }

    private function transform(array $data): void
    {

    }

}