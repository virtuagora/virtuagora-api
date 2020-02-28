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
}