<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Category;
use Mockery;
use Illuminate\Http\Request;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_category()
    {
        $mock_request = Mockery::mock(Request::class, function (Mockery\MockInterface $mock) {
            $mock->shouldReceive('validate')
                ->with(['category' => 'required|string']);
            $mock->shouldReceive('input')
                ->with('category')
                ->andReturn('1234');
        });
        $ctg_model = new Category();
        $ctg_model->storeNewCategory($mock_request);
        $this->assertDatabaseHas(Category::class, ['category' => '1234']);
    }

    public function test_update_category()
    {
        Category::factory()->create();

        $mock_request = Mockery::mock(Request::class, function (Mockery\MockInterface $mock) {
            $mock->shouldReceive('validate')
                ->with(['category' => 'required|string']);
            $mock->shouldReceive('input')
                ->with('category')
                ->andReturn('update_ctg');
        });
        $ctg_model = new Category();
        $ctg_model->updateCategory($mock_request, 1);
        $this->assertDatabaseHas(Category::class, ['category' => 'update_ctg']);
    }

    public function test_find_category_by_id()
    {
        Category::factory()->create();
        $ctg_model = new Category();
        $category = $ctg_model->findCategoryById(1);
        $this->assertDatabaseHas(Category::class, ['category' => $category]);
    }

    public function test_delete_by_id()
    {
        Category::factory()->create();
        $ctg_model = new Category();
        $category = $ctg_model->find(1);
        $ctg_model->destroyById(1);
        $this->assertDatabaseCount(Category::class, 0);
        $this->assertDeleted($category);
    }
}
