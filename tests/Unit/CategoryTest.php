<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_has_many_books()
    {
        $category = Category::factory()->create();
        $book = Book::factory()->create(['category_id' => $category->id]);

        $this->assertTrue($category->books->contains($book));
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $category->books);
    }
}
