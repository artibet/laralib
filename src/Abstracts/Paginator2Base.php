<?php

namespace Artibet\Laralib\Abstracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;


abstract class Paginator2Base
{
  protected Request $request;

  // ---------------------------------------------------------------------------------------
  // constructor
  // ---------------------------------------------------------------------------------------
  public function __construct(request $request)
  {
    $this->request = $request;
  }

  // ---------------------------------------------------------------------------------------
  // Abstract overriden methods
  // ---------------------------------------------------------------------------------------
  protected abstract function columns(): array;
  protected abstract function query(): Builder;
  protected abstract function resourceClass(): string;

  // ---------------------------------------------------------------------------------------
  // Default primary key - override if needed
  // ---------------------------------------------------------------------------------------
  protected function primaryKey(): string
  {
    return 'id';
  }

  // ---------------------------------------------------------------------------------------
  // Default sorting direction - override if needed
  // ---------------------------------------------------------------------------------------
  protected function defaultSortingDirection(): string
  {
    return 'asc';
  }


  // ---------------------------------------------------------------------------------------
  // Global filter
  // ---------------------------------------------------------------------------------------
  protected function applyGlobalFilter($query)
  {
    $value = $this->request->globalFilter;
    if (!$value) return;

    // Wrap the entire global search in a single WHERE block
    // to prevent OR logic from leaking into other query constraints.
    $query->where(function ($subQuery) use ($value) {
      foreach ($this->columns() as $column) {

        // 1. Prioritize 'global', then fallback to 'search'.
        // Note: We ignore 'filter' here because column-specific filters
        // are usually too strict (exact IDs) for a general text search.
        $closure = $column['global'] ?? $column['search'] ?? null;

        // 2. Use orWhere so that it matches Column A OR Column B OR Column C
        if (is_callable($closure)) {
          $subQuery->orWhere(function ($q) use ($closure, $value) {
            $closure($q, $value);
          });
        }
      }
    });
  }

  // ---------------------------------------------------------------------------------------
  // Colulmn filters
  // ---------------------------------------------------------------------------------------
  protected function applyColumnFilters($query)
  {
    $columnFilters = json_decode($this->request->columnFilters, true) ?? [];
    $columnDefs = $this->columns();

    foreach ($columnFilters as $filter) {
      $id = $filter['id'];
      $value = $filter['value'];

      // 1. Identify the best closure to use: 'filter' first, then fallback to 'search'
      $closure = $columnDefs[$id]['filter'] ?? $columnDefs[$id]['search'] ?? null;

      // 2. Skip if no valid closure is found
      if (!is_callable($closure)) {
        continue;
      }

      // 3. Wrap in a where block for SQL parameter grouping
      $query->where(function ($subQuery) use ($closure, $value) {
        $closure($subQuery, $value);
      });
    }
  }

  // ---------------------------------------------------------------------------------------
  // Sorting 
  // ---------------------------------------------------------------------------------------
  protected function applySorting($query)
  {
    $sorting = json_decode($this->request->sorting, true) ?? [];
    $columnDefs = $this->columns();

    // Create an orderBy clause for each sorting column
    foreach ($sorting as $sortInst) {
      $id = $sortInst['id'];
      $direction = $sortInst['desc'] ? 'desc' : 'asc';

      if (!isset($columnDefs[$id])) continue;

      $col = $columnDefs[$id];

      // 1. Check for custom closure
      if (isset($col['sort']) && is_callable($col['sort'])) {
        $col['sort']($query, $direction);
      }
    }

    // Tie-breaker: Always sort by ID last if not already sorted, 
    // to keep pagination stable.
    $query->orderBy($this->primaryKey(), $this->defaultSortingDirection());
  }


  // ---------------------------------------------------------------------------------------
  // Count Total Rows
  // ---------------------------------------------------------------------------------------
  protected function countRows($query)
  {
    $countQuery = clone $query;
    return $countQuery->count();
  }

  // ---------------------------------------------------------------------------------------
  // Create and return the footer
  // ---------------------------------------------------------------------------------------
  protected function buildFooter()
  {
    $footerArray = [];

    // 1. Prepare the filtered query base
    $footerQueryBase = clone $this->query();
    $this->applyGlobalFilter($footerQueryBase);
    $this->applyColumnFilters($footerQueryBase);

    // 2. Iterate through columns and execute footer closures
    foreach ($this->columns() as $id => $column) {
      if (isset($column['footer']) && is_callable($column['footer'])) {
        // We clone the query for each column so aggregate 
        // functions don't stack or interfere with each other
        $footerArray[$id] = $column['footer'](clone $footerQueryBase);
      } else {
        // Default to empty string if no footer is defined
        $footerArray[$id] = '';
      }
    }

    return $footerArray;
  }

  // ---------------------------------------------------------------------------------------
  // Get query after applying sorting and filtering
  // ---------------------------------------------------------------------------------------
  public function getQuery(): Builder
  {
    $query = clone $this->query();
    $this->applyGlobalFilter($query);
    $this->applyColumnFilters($query);
    $this->applySorting($query);
    return $query;
  }

  // ---------------------------------------------------------------------------------------
  // Return response array
  // ---------------------------------------------------------------------------------------
  public function response(): array
  {
    $resourceClass = $this->resourceClass();
    $dataQuery = $this->getQuery();
    $page = (int)($this->request->page ?? 1);
    $pageSize = (int)($this->request->pageSize ?? 10);
    $data = $dataQuery->skip(($page - 1) * $pageSize)->take($pageSize)->get();

    return [
      'data' => $resourceClass::collection($data),
      'total' => $this->getQuery()->count(),
      'page' => $page,
      'page_size' => $pageSize,
      'footer' => $this->buildFooter(),
    ];
  }
}
