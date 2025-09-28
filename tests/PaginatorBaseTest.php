<?php

namespace Artibet\Laralib\Tests;

use Artibet\Laralib\Abstracts\PaginatorBase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Http\Resources\Json\JsonResource;
use Orchestra\Testbench\TestCase;

// Fake model for testing
class FakeModel extends Model
{
  use HasFactory;

  protected $table = 'fake_models';
  protected $guarded = [];
}

// Fake resource
class FakeResource extends JsonResource
{
  public function toArray($request)
  {
    return [
      'id' => $this->id,
      'name' => $this->name,
      'value' => $this->value,
      'created_at' => $this->created_at->format('Y-m-d'),
    ];
  }
}

// Concrete paginator for testing
class FakePaginator extends PaginatorBase
{
  protected function columns(): array
  {
    return [
      ['id' => 'name', 'type' => 'string'],
      ['id' => 'value', 'type' => 'number', 'footer' => [
        'type' => 'sum',
        'format' => 'float',
        'postfix' => ' pts'
      ]],
      ['id' => 'created_at', 'type' => 'date'],
    ];
  }

  protected function query(): Builder
  {
    return FakeModel::query();
  }

  protected function resourceClass(): string
  {
    return FakeResource::class;
  }
}

class PaginatorBaseTest extends TestCase
{
  use RefreshDatabase;

  protected function setUp(): void
  {
    parent::setUp();

    // Migrate table for fake model
    $this->app['db']->connection()->getSchemaBuilder()->create('fake_models', function ($table) {
      $table->id();
      $table->string('name');
      $table->integer('value');
      $table->timestamps();
    });

    // Seed data
    FakeModel::create(['name' => 'Alice', 'value' => 10, 'created_at' => now()]);
    FakeModel::create(['name' => 'Bob', 'value' => 20, 'created_at' => now()]);
    FakeModel::create(['name' => 'Charlie', 'value' => 30, 'created_at' => now()]);
  }

  // ---------------------------------------------------------------------------------------
  // Test global filter on strings
  // ---------------------------------------------------------------------------------------
  public function test_it_applies_global_filter_on_strings()
  {
    $request = new Request([
      'globalFilter' => 'Bob',
      'page' => 1,
      'pageSize' => 10,
      'sorting' => json_encode([]),
      'columnFilters' => json_encode([]),
    ]);

    $paginator = new FakePaginator($request);

    $response = $paginator->response();

    $this->assertCount(1, $response['data']);
    $this->assertEquals('Bob', $response['data'][0]['name']);
  }

  // ---------------------------------------------------------------------------------------
  // Test global filter on numbers
  // ---------------------------------------------------------------------------------------
  public function test_it_applies_column_filter_on_number()
  {
    $request = new Request([
      'globalFilter' => null,
      'page' => 1,
      'pageSize' => 10,
      'sorting' => json_encode([]),
      'columnFilters' => json_encode([
        ['id' => 'value', 'value' => 30]
      ]),
    ]);

    $paginator = new FakePaginator($request);

    $response = $paginator->response();

    $this->assertCount(1, $response['data']);
    $this->assertEquals(30, $response['data'][0]['value']);
  }

  // ---------------------------------------------------------------------------------------
  // Test sorting
  // ---------------------------------------------------------------------------------------
  public function test_it_applies_sorting()
  {
    $request = new Request([
      'page' => 1,
      'pageSize' => 10,
      'sorting' => json_encode([
        ['id' => 'value', 'desc' => true]
      ]),
      'globalFilter' => null,
      'columnFilters' => json_encode([]),
    ]);

    $paginator = new FakePaginator($request);

    $response = $paginator->response();

    $this->assertEquals(30, $response['data'][0]['value']); // Highest first
  }

  // ---------------------------------------------------------------------------------------
  // Test footer
  // ---------------------------------------------------------------------------------------
  public function test_it_builds_footer_with_sum()
  {
    $request = new Request([
      'page' => 1,
      'pageSize' => 10,
      'sorting' => json_encode([]),
      'globalFilter' => null,
      'columnFilters' => json_encode([]),
    ]);

    $paginator = new FakePaginator($request);

    $response = $paginator->response();

    $this->assertEquals('60,00 pts', $response['footer']['value']); // sum of 10+20+30 formatted
  }
}
