<?php

namespace Tests\Unit;

use App\Models\Author;
use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorTest extends TestCase
{
    use RefreshDatabase;

    public function test_author_has_many_books()
    {
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);

        $this->assertTrue($author->books->contains($book));
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $author->books);
    }
}
