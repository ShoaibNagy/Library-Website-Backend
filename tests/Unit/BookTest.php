<?php

namespace Tests\Unit;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    public function test_book_belongs_to_author()
    {
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);

        $this->assertInstanceOf(Author::class, $book->author);
        $this->assertEquals($author->id, $book->author->id);
    }

    public function test_book_belongs_to_category()
    {
        $category = Category::factory()->create();
        $book = Book::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(Category::class, $book->category);
        $this->assertEquals($category->id, $book->category->id);
    }

    public function test_book_available_copies_decrement()
    {
        $book = Book::factory()->create(['available_copies' => 5]);
        
        $book->decrement('available_copies');
        
        $this->assertEquals(4, $book->fresh()->available_copies);
    }

    public function test_book_scope_available()
    {
        Book::factory()->create(['available_copies' => 1]);
        Book::factory()->create(['available_copies' => 0]);

        $this->assertCount(1, Book::available()->get());
    }
}
