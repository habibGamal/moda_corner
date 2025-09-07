<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Inertia\Response;

/**
 * A service for handling feed scroll pagination for any model type.
 * This allows for implementing infinite scroll and pagination across different models.
 */
class FeedScrollService
{
    /**
     * The query builder instance
     *
     * @var Builder|null
     */
    protected $query = null;

    /**
     * Section identifier for this feed
     *
     * @var string|null
     */
    protected $sectionId = null;

    /**
     * Relationships to eager load
     *
     * @var array
     */
    protected $with = [];

    /**
     * Number of items per page
     *
     * @var int
     */
    protected $perPage = 5;

    /**
     * Column and direction for ordering
     *
     * @var array
     */
    protected $orderBy = ['id', 'desc'];

    /**
     * Initialize a new feed for a specific model.
     *
     * @param string $modelClass The fully qualified class name of the model
     * @return $this
     */
    public function model(string $modelClass): self
    {
        $this->resetState();
        $this->query = $modelClass::query();
        return $this;
    }

    /**
     * Initialize a feed from an existing builder query.
     *
     * @param Builder $query The query builder instance
     * @return $this
     */
    public function fromQuery(Builder $query): self
    {
        $this->resetState();
        $this->query = $query;
        return $this;
    }

    /**
     * Set the section identifier.
     *
     * @param string $sectionId A unique identifier for this section/feed
     * @return $this
     */
    public function forSection(string $sectionId): self
    {
        $this->sectionId = $sectionId;
        return $this;
    }

    /**
     * Set relationships to eager load.
     *
     * @param array $relationships Relationships to eager load
     * @return $this
     */
    public function with(array $relationships): self
    {
        $this->with = $relationships;
        return $this;
    }

    /**
     * Set the number of items per page.
     *
     * @param int $perPage Number of items per page
     * @return $this
     */
    public function perPage(int $perPage): self
    {
        $this->perPage = $perPage;
        return $this;
    }

    /**
     * Set the ordering parameters.
     *
     * @param string $column Column to order by
     * @param string $direction Direction to order (asc or desc)
     * @return $this
     */
    public function orderBy(string $column, string $direction = 'desc'): self
    {
        $this->orderBy = [$column, $direction];
        return $this;
    }

    /**
     * Apply a where condition to the query.
     *
     * @param string $column The column to filter on
     * @param mixed $operator The operator or value
     * @param mixed $value The value (if operator is provided)
     * @return $this
     */
    public function where(string $column, $operator, $value = null): self
    {
        if ($this->query === null) {
            throw new \RuntimeException("Model must be set before applying conditions.");
        }

        if ($value === null) {
            $this->query->where($column, $operator);
        } else {
            $this->query->where($column, $operator, $value);
        }

        return $this;
    }

    /**
     * Apply multiple where conditions at once.
     *
     * @param array $conditions Array of conditions to apply
     * @return $this
     */
    public function whereConditions(array $conditions): self
    {
        if ($this->query === null) {
            throw new \RuntimeException("Model must be set before applying conditions.");
        }

        foreach ($conditions as $column => $value) {
            if (is_array($value) && count($value) === 3) {
                // Handle custom operator conditions [column, operator, value]
                $this->query->where($value[0], $value[1], $value[2]);
            } else {
                // Handle simple equality conditions
                $this->query->where($column, $value);
            }
        }

        return $this;
    }

    /**
     * Execute the query and get the paginated feed.
     *
     * @return array The data and pagination keys
     */
    public function get(): array
    {
        if ($this->query === null) {
            throw new \RuntimeException("Query has not been initialized. Call model() or fromQuery() first.");
        }

        if ($this->sectionId === null) {
            throw new \RuntimeException("Section ID must be set. Call forSection() first.");
        }

        // Apply ordering if specified
        if (!empty($this->orderBy) && count($this->orderBy) === 2) {
            $this->query->orderBy($this->orderBy[0], $this->orderBy[1]);
        }

        // If there are relationships to eager load, add them to the query
        if (!empty($this->with)) {
            $this->query->with($this->with);
        }

        // Generate section-based keys for consistent access in frontend
        $sectionKey = "section_{$this->sectionId}_page";
        $dataKey = "{$sectionKey}_data";
        $paginationKey = "{$sectionKey}_pagination";

        // Execute the pagination
        $paginatedResults = $this->query->paginate($this->perPage, pageName: $sectionKey);

        // Return the data and pagination in the format expected by the frontend
        return [
            $dataKey => inertia()->merge($paginatedResults->items()),
            $paginationKey => Arr::except($paginatedResults->toArray(), ['data']),
        ];
    }

    /**
     * Add the feed data directly to an existing Inertia response.
     *
     * @param Response $response The Inertia response to add data to
     * @return Response The updated Inertia response
     */
    public function addToResponse(Response $response): Response
    {
        $feedData = $this->get();

        foreach ($feedData as $key => $value) {
            $response->with($key, $value);
        }

        return $response;
    }

    /**
     * Create a paginated feed for a specific model instance (legacy method).
     *
     * @param string $modelClass The fully qualified class name of the model
     * @param string $sectionId A unique identifier for this section/feed
     * @param array $conditions Query conditions to apply
     * @param array $with Relationships to eager load
     * @param array $orderBy Ordering parameters [column, direction]
     * @param int $perPage Number of items per page
     * @return array The data and pagination keys
     *
     * @deprecated Use the fluent interface instead: model()->forSection()->whereConditions()->with()->orderBy()->perPage()->get()
     */
    public function createModelFeed(
        string $modelClass,
        string $sectionId,
        array $conditions = [],
        array $with = [],
        array $orderBy = ['id', 'desc'],
        int $perPage = 5
    ): array {
        return $this->model($modelClass)
            ->forSection($sectionId)
            ->whereConditions($conditions)
            ->with($with)
            ->orderBy($orderBy[0], $orderBy[1])
            ->perPage($perPage)
            ->get();
    }

    /**
     * Get a feed for a related model through a relationship.
     *
     * @param Model $model The parent model instance
     * @param string $relation The name of the relation
     * @param string $sectionId A unique identifier for this section/feed
     * @param array $with Additional relationships to eager load
     * @param int $perPage Number of items per page
     * @return array The data and pagination keys
     */
    public function getRelatedModelFeed(
        Model $model,
        string $relation,
        string $sectionId,
        array $with = [],
        int $perPage = 5
    ): array {
        if (!method_exists($model, $relation)) {
            throw new \InvalidArgumentException("Relation {$relation} does not exist on " . get_class($model));
        }

        $query = $model->$relation();

        return $this->fromQuery($query)
            ->forSection($sectionId)
            ->with($with)
            ->perPage($perPage)
            ->get();
    }

    /**
     * Reset the internal state of the service.
     */
    protected function resetState(): void
    {
        $this->query = null;
        $this->sectionId = null;
        $this->with = [];
        $this->perPage = 5;
        $this->orderBy = ['id', 'desc'];
    }
}
