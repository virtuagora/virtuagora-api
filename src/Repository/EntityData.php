<?php
declare(strict_types=1);

namespace App\Repository;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use App\Util\Paginator;

class EntityData
{
    /**
     * @var Model|null
     */
    private $model;

    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var string
     */
    private $state;

    /**
     * @var Paginator|null
     */
    private $paginator;

    /**
     * @var array
     */
    private $metadata;

    /**
     * @var array
     */
    private $warnings;

    /**
     * @param string      $state
     * @param Model|null  $model
     */
    public function __construct(string $state, ?Model $model = null)
    {
        $this->state = $state;
        if (isset($model)) {
            $this->setModel($model);
        } else {
            $this->model = null;
            $this->collection = new Collection();
        }
        $this->paginator = null;
        $this->metadata = [];
        $this->warnings = [];
    }

    /**
     * @return self
     */
    public function setState(string $state): self
    {
        $this->state = $state;
        return $this;
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @return self
     */
    public function setModel(Model $model): self
    {
        $this->model = $model;
        $this->collection = new Collection();
        $this->collection->push($model);
        return $this;
    }

    /**
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * @return self
     */
    public function setCollection(Collection $collection): self
    {
        $this->collection = $collection;
        $this->model = $collection->first();
        return $this;
    }

    /**
     * @return Collection
     */
    public function getCollection(): Collection
    {
        return $this->collection;
    }

    /**
     * @return self
     */
    public function setWarnings(array $warnings): self
    {
        $this->warnings = $warnings;
        return $this;
    }

    /**
     * @return self
     */
    public function addWarning(string $code, string $description): self
    {
        $this->warnings[$code] = $description;
        return $this;
    }

    /**
     * @return array
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }

    /**
     * @return self
     */
    public function setMetadata(array $metadata): self
    {
        $this->metadata = $metadata;
        return $this;
    }

    /**
     * @return self
     */
    public function addMetadata(array $metadata): self
    {
        $this->metadata = array_merge($this->metadata, $metadata);
        return $this;
    }

    /**
     * @return array
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * @return self
     */
    public function setPaginator(Paginator $paginator, bool $refresh = true): self
    {
        $this->paginator = $paginator;
        if ($refresh) {
            $this->setCollection($paginator->getItems());
        }
        return $this;
    }

    /**
     * @return Paginator
     */
    public function getPaginator(): Paginator
    {
        return $this->paginator;
    }
}
