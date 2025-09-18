<?php

namespace Artibet\Laralib\Abstracts;

use Artibet\Laralib\Support\Numbers;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class PaginatorBase
{

  // ---------------------------------------------------------------------------------------
  // Abstract overriden methods
  // ---------------------------------------------------------------------------------------
  protected abstract function getColumns(): array;
  protected abstract function getQuery(): Builder;
  protected abstract function getResourceClass(): string;

  // ---------------------------------------------------------------------------------------
  // Default primary key - override if needed
  // ---------------------------------------------------------------------------------------
  protected function getPrimaryKey(): string
  {
    return 'id';
  }

  // ---------------------------------------------------------------------------------------
  // Default sorting direction - override if needed
  // ---------------------------------------------------------------------------------------
  protected function getDefaultSortDirection(): string
  {
    return 'asc';
  }


  // ---------------------------------------------------------------------------------------
  // Global filter
  // ---------------------------------------------------------------------------------------
  protected function applyGlobalFilter(Request $request, $query)
  {
    $globalFilter = $request->globalFilter;
    if (!$globalFilter) return;

    $query->where(function ($q) use ($globalFilter) {
      foreach ($this->getColumns() as $column) {
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
  protected function applyColumnFilters(Request $request, $query)
  {
    $columnFilters = json_decode($request->columnFilters);

    if (!$columnFilters) return;

    foreach ($columnFilters as $columnFilter) {
      $query->where(function ($q) use ($columnFilter) {
        foreach ($this->getColumns() as $column) {
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
  protected function applySorting(Request $request, $query)
  {
    $sorting = json_decode($request->sorting);

    // Create an orderBy clause for each sorting column
    foreach ($sorting as $sortingColumn) {
      $columnName = $sortingColumn->id;
      $sortDirection = $sortingColumn->desc ? 'desc' : 'asc';
      $query->orderBy($columnName, $sortDirection);
    }

    // Add default sorting on id
    $query->orderBy($this->getPrimaryKey(), $this->getDefaultSortDirection());
  }


  // ---------------------------------------------------------------------------------------
  // Get Data
  // ---------------------------------------------------------------------------------------
  protected function getRows(Request $request)
  {
    $dataQuery = clone $this->getQuery();
    $this->applyGlobalFilter($request, $dataQuery);
    $this->applyColumnFilters($request, $dataQuery);
    $this->applySorting($request, $dataQuery);

    return $dataQuery->skip(($request->page - 1) * $request->pageSize)->take($request->pageSize)->get();
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
  protected function getFooter(Request $request)
  {
    $footerArray = [];

    $footerQuery = clone $this->getQuery();
    $this->applyGlobalFilter($request, $footerQuery);
    $this->applyColumnFilters($request, $footerQuery);

    foreach ($this->getColumns() as $column) {
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
  public function buildQuery(Request $request): Builder
  {
    $query = clone $this->getQuery();
    $this->applyGlobalFilter($request, $query);
    $this->applyColumnFilters($request, $query);
    $this->applySorting($request, $query);
    return $query;
  }

  // ---------------------------------------------------------------------------------------
  // Return response array
  // ---------------------------------------------------------------------------------------
  public function response(Request $request): array
  {
    $resourceClass = $this->getResourceClass();
    $dataQuery = $this->buildQuery($request);
    $data = $dataQuery->skip(($request->page - 1) * $request->pageSize)->take($request->pageSize)->get();

    return [
      'data' => $resourceClass::collection($data),
      'total' => $this->buildQuery($request)->count(),
      'page' => (int)($request->page),
      'page_size' => (int)($request->pageSize),
      'footer' => $this->getFooter($request),
    ];
  }
}
