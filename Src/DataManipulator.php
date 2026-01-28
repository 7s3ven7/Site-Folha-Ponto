<?php

namespace Src;

use stdClass;

class DataManipulator
{

    private array $response;

    private array $variablesTypes;

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

        if (!isset($data)) {
            $this->response = ['error' => ['message' => 'Data missing', 'Data' => $lastKey]];
        }

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
                        $this->response = ['error' => ['message' => 'Data must be a int']];
                        return;
                    }

                    if (!ctype_digit($data)) {
                        $this->response = ['error' => ['message' => 'Data must be a int not float']];
                        return;
                    }

                    $this->variablesTypes[$patch[count($patch) - 2]][$lastKey] = (int)$data;

                    return;
                case 'float':

                    if (!is_numeric($data)) {
                        $this->response = ['error' => ['message' => 'Data must be a float']];
                        return;
                    }

                    $this->variablesTypes[$patch[count($patch) - 2]][$lastKey] = (float)$data;

                    return;
                case 'bool':

                    $bool = filter_var($data, FILTER_VALIDATE_BOOLEAN);

                    if (!is_bool($bool)) {
                        $this->response = ['error' => ['message' => 'Data must be a boolean']];
                        return;
                    }

                    $this->variablesTypes[$patch[count($patch) - 2]][$lastKey] = $data;

                    return;
                case 'string':

                    if (!is_string($data)) {
                        $this->response = ['error' => ['message' => 'Data must be a string']];
                        return;
                    }

                    $this->variablesTypes[$patch[count($patch) - 2]][$lastKey] = $data;

                    return;
                case 'array':
                    if (!is_array($data)) {
                        $this->response = ['error' => ['message' => 'Data must be an array']];
                        return;
                    }

                    $this->variablesTypes[$patch[count($patch) - 2]][$lastKey] = $data;

                    return;
                case 'object':

                    $dataManipulator = new DataManipulator();

                    $object = $dataManipulator->createObject(json_encode($data));

                    $this->variablesTypes[$patch[count($patch) - 2]][$lastKey] = $object;

                    return;
            }
        }
    }

    public function createObject(string $data): object|array
    {
        $data = $this->filter($data);

        if ($data === null) {
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