<?php

namespace App\Service\Manipulator;

class TransformObject
{

    public function returnArray(object $obj): array
    {

        foreach ($obj as $key => $value) {
            if (is_object($value)) {
                $arrayAsReturn[$key] = $this->returnArray($value);
            } else {
                $arrayAsReturn[$key] = $value;
            }
        }

        return $arrayAsReturn ?? [];
    }

}