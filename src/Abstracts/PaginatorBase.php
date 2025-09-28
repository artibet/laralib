<?php

namespace Artibet\Laralib\Abstracts;

use Artibet\Laralib\Support\Numbers;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class PaginatorBase
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
    $globalFilter = $this->request->globalFilter;
    if (!$globalFilter) return;

    $query->where(function ($q) use ($globalFilter) {
      foreach ($this->columns() as $column) {
        if ($column['type'] == 'string') {
          $q->orWhere($column['id'], 'like', "%$globalFilter%");
        } else if ($column['type'] == 'number' && is_numeric($globalFilter)) {
          $q->orWhere($column['id'], '=', "$globalFilter");
        } else if ($column['type'] == 'date') {
          $d = null;
          try {
            $d = Carbon::createFromFormat('d/m/Y', $globalFilter);
          } catch (Exception $e) {
            continue;
          }
          $q->orWhereDate($column['id'], '=', $d);
        }
      }
    });
  }

  // ---------------------------------------------------------------------------------------
  // Colulmn filters
  // ---------------------------------------------------------------------------------------
  protected function applyColumnFilters($query)
  {
    $columnFilters = json_decode($this->request->columnFilters);

    if (!$columnFilters) return;

    foreach ($columnFilters as $columnFilter) {
      $query->where(function ($q) use ($columnFilter) {
        foreach ($this->columns() as $column) {
          // scan columns to find the one with filter - we need the type
          if ($column['id'] !== $columnFilter->id) continue;

          // column found
          if ($column['type'] == 'string') {
            if (array_key_exists('exact', $column) && $column['exact']) {
              $q->orWhere($column['id'], '=', "$columnFilter->value");
            } else {
              $q->orWhere($column['id'], 'like', "$columnFilter->value%");
            }
          } else if ($column['type'] == 'number' && is_numeric($columnFilter->value)) {
            $q->orWhere($column['id'], '=', "$columnFilter->value");
          } else if ($column['type'] == 'date') {
            $d = null;
            try {
              $d = Carbon::createFromFormat('d/m/Y', $columnFilter->value);
            } catch (Exception $e) {
              continue;
            }
            $q->orWhereDate($column['id'], '=', $d);
          }

          // Do not check other columns
          return;
        }
      });
    }
  }

  // ---------------------------------------------------------------------------------------
  // Sorting 
  // ---------------------------------------------------------------------------------------
  protected function applySorting($query)
  {
    $sorting = json_decode($this->request->sorting);

    // Create an orderBy clause for each sorting column
    foreach ($sorting as $sortingColumn) {
      $columnName = $sortingColumn->id;
      $sortDirection = $sortingColumn->desc ? 'desc' : 'asc';
      $query->orderBy($columnName, $sortDirection);
    }

    // Add default sorting on id
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

    $footerQuery = clone $this->query();
    $this->applyGlobalFilter($footerQuery);
    $this->applyColumnFilters($footerQuery);

    foreach ($this->columns() as $column) {
      if (array_key_exists('footer', $column)) {
        $columnFooter = $column['footer'];
        $footer  = '';
        if ($columnFooter['type'] == 'label') {
          $footer = $columnFooter['value'];
        } else if ($columnFooter['type'] == 'sum') {
          $q = clone $footerQuery;
          $footer = $q->sum($column['id']);
          $postfix = array_key_exists('postfix', $columnFooter) ? $columnFooter['postfix'] : '';
          if ($columnFooter['format'] == 'currency') {
            $footer = Numbers::formatCurrency($footer) . $postfix;
          } else if ($columnFooter['format'] == 'float') {
            $footer = number_format($footer, 2, ',', '.') . $postfix;
          }
        }
        $footerArray[$column['id']] = $footer;
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
    $data = $dataQuery->skip(($this->request->page - 1) * $this->request->pageSize)->take($this->request->pageSize)->get();

    return [
      'data' => $resourceClass::collection($data),
      'total' => $this->getQuery()->count(),
      'page' => (int)($this->request->page),
      'page_size' => (int)($this->request->pageSize),
      'footer' => $this->buildFooter(),
    ];
  }
}
