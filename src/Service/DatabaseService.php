<?php
declare(strict_types=1);

namespace App\Service;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Schema\Builder as SchemaBuilder;
use Illuminate\Database\Eloquent\Model;

interface DatabaseService
{
    /**
     * @param string      $table
     * @param string|null $connection
     * @return QueryBuilder
     */
    public function table(string $table, ?string $connection): QueryBuilder;

    /**
     * @param string|null $connection
     * @return SchemaBuilder
     */
    public function schema(?string $connection): SchemaBuilder;

    /**
     * @param string   $model
     * @param string[] $with
     * @return QueryBuilder
     */
    public function query(string $model, ?array $with): QueryBuilder;

    /**
     * @param string $model
     * @param array  $attributes
     * @return Model
     */
    public function create(string $model, array $attributes): Model

    /**
     * @param string $model
     * @param array  $attributes
     * @return Model
     */
    public function createAndSave(string $model, array $attributes): Model

    // TODO
    // public function findDuplicatedFields($model, $instance, $fields):
}
