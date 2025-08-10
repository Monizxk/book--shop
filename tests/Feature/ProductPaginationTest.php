<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_index_returns_paginated_response()
    {
        // Create 25 products
        Product::factory()->count(25)->create(['hidden' => false]);

        $response = $this->getJson('/api/products?page=1&per_page=10');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'products',
                'current_page',
                'last_page',
                'per_page',
                'total'
            ])
            ->assertJson([
                'current_page' => 1,
                'per_page' => 10,
                'total' => 25,
                'last_page' => 3
            ]);

        assert(count($response->json('products')) === 10);
    }

    public function test_products_index_uses_default_per_page()
    {
        // Create 15 products
        Product::factory()->count(15)->create(['hidden' => false]);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJson([
                'per_page' => 10,
                'total' => 15,
                'last_page' => 2
            ]);
    }

    public function test_sale_products_returns_paginated_response()
    {
        // Create 20 sale products
        Product::factory()->count(20)->create([
            'hidden' => false,
            'is_on_sale' => true
        ]);

        $response = $this->getJson('/api/products/sale?page=2&per_page=5');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'products',
                'current_page',
                'last_page',
                'per_page',
                'total'
            ])
            ->assertJson([
                'current_page' => 2,
                'per_page' => 5,
                'total' => 20,
                'last_page' => 4
            ]);

        assert(count($response->json('products')) === 5);
    }

    public function test_way_products_returns_paginated_response()
    {
        // Create 12 way products
        Product::factory()->count(12)->create([
            'hidden' => false,
            'is_on_way' => true
        ]);

        $response = $this->getJson('/api/products/way?page=1&per_page=8');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'products',
                'current_page',
                'last_page',
                'per_page',
                'total'
            ])
            ->assertJson([
                'current_page' => 1,
                'per_page' => 8,
                'total' => 12,
                'last_page' => 2
            ]);

        assert(count($response->json('products')) === 8);
    }

    public function test_search_products_returns_paginated_response()
    {
        // Create products with specific titles
        Product::factory()->create(['title' => 'Test Book 1', 'hidden' => false]);
        Product::factory()->create(['title' => 'Test Book 2', 'hidden' => false]);
        Product::factory()->create(['title' => 'Test Book 3', 'hidden' => false]);
        Product::factory()->create(['title' => 'Other Book', 'hidden' => false]);

        $response = $this->getJson('/api/products/search?q=Test&page=1&per_page=2');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'products',
                'current_page',
                'last_page',
                'per_page',
                'total'
            ])
            ->assertJson([
                'current_page' => 1,
                'per_page' => 2,
                'total' => 3,
                'last_page' => 2
            ]);

        assert(count($response->json('products')) === 2);
    }
} 