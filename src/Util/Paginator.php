<?php
declare(strict_types=1);

namespace App\Util;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

class Paginator
{
    /**
     * @var Collection|null
     */
    private $items;

    /**
     * @var int
     */
    private $total;

    /**
     * @var array
     */
    protected $params;

    /**
     * @var string
     */
    protected $url;

    /**
     * @param Builder $query
     * @param array $params
     * @param string|null $url
     */
    public function __construct(
        Builder $query,
        array $params,
        string $url = ''
    ) {
        $this->params = array_merge([
            'size' => 25,
            'offset' => 0,
        ], $params);
        $this->total = $query->toBase()->getCountForPagination();
        $size = $params['size'];
        $offset = $params['offset'];
        $sortCol = $params['sort'] ?? null;
        if (isset($sortCol)) {
            if ($sortCol == 'random') {
                $params['size'] = $size = min($size, 50);
                if ($this->total < 4 * $size) {
                    $this->items = $query->inRandomOrder()->take($size)->get();
                } else {
                    $take = $size - 1;
                    $ceil = $this->total;
                    $bseQ = (clone $query)->offset(rand(0, $ceil))->take(1);
                    for ($i = 0; $i < $take; $i++) {
                        $auxQ = (clone $query)->offset(rand(0, $ceil))->take(1);
                        $bseQ->union($auxQ);
                    }
                    $this->items = $bseQ->get();
                }
            } else {
                $sortDir = $params['direction'] ?? 'asc';
                $this->items = $query
                    ->offset($offset)->take($size)
                    ->orderBy($sortCol, $sortDir)
                    ->get();
            }
        } else {
            $this->items = $query->offset($offset)->take($size)->get();
        }
        $this->url = $url;
    }

    /**
     * @return self
     */
    public function setUrl(string $uri): self
    {
        $this->url = $uri;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    /**
     * @return Collection
     */
    public function addItem($new): Collection
    {
        return $this->items->push($new);
    }

    /**
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @return bool
     */
    public function hasMorePages(): bool
    {
        return $this->params['offset'] + $this->params['size'] < $this->total;
    }

    /**
     * @return string
     */
    public function urlWithOffset($offset): string
    {
        $params = $this->params;
        $params['offset'] = $offset;
        return $this->uri . '?' . http_build_query($params);
    }

    /**
     * @return array
     */
    public function getPaginationInfo(): array
    {
        return [
            'offset' => $this->params['offset'],
            'size' => $this->params['size'],
            'total' => $this->total,
        ];
    }

    /**
     * @return array
     */
    public function getLinks(): array
    {
        $links = [];
        if ($this->hasMorePages()) {
            $links['next'] = $this->urlWithOffset(
                $this->params['offset'] + $this->size
            );
        }
        if ($this->offset > 0) {
            $links['prev'] = $this->urlWithOffset(
                max(0, $this->params['offset'] - $this->size)
            );
        }
        return $links;
    }
}
