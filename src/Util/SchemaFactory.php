<?php

namespace App\Util;

use Opis\JsonSchema\Schema;

class SchemaFactory
{
    static public function fromFile(string $schema): Schema
    {
        $path = __DIR__ . '../../app/' . $schema . '.json';
        return Schema::fromJsonString(file_get_contents($path));
    }

    static public function fromArray(array $schema): Schema
    {
        return new Schema(self::arrayToObject($schema));
    }

    static private function arrayToObject(array $array): object
    {
        $object = new stdClass();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $value = convertToObject($value);
            }
            $object->$key = $value;
        }
        return $object;
    }
}