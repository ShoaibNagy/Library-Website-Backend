<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_list_books()
    {
        Book::factory()->count(3)->create();

        $response = $this->getJson('/api/books');

        $response->assertStatus(200)
                 ->assertJsonCount(3, 'data');
    }

    public function test_librarian_can_create_book()
    {
        $user = User::factory()->create(['role' => 'librarian']);
        $author = Author::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/books', [
            'title' => 'New Book',
            'isbn' => '1234567890',
            'author_id' => $author->id,
            'category_id' => $category->id,
            'total_copies' => 5,
            'available_copies' => 5,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('books', ['title' => 'New Book']);
    }

    public function test_member_cannot_create_book()
    {
        $user = User::factory()->create(['role' => 'member']);
        
        $response = $this->actingAs($user)->postJson('/api/books', [
            'title' => 'New Book',
        ]);

        $response->assertStatus(403);
    }

    public function test_librarian_can_update_book()
    {
        $user = User::factory()->create(['role' => 'librarian']);
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->putJson("/api/books/{$book->id}", [
            'title' => 'Updated Title',
            'isbn' => $book->isbn,
            'author_id' => $book->author_id,
            'category_id' => $book->category_id,
            'total_copies' => $book->total_copies,
            'available_copies' => $book->available_copies,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('books', ['id' => $book->id, 'title' => 'Updated Title']);
    }

    public function test_librarian_can_delete_book()
    {
        $user = User::factory()->create(['role' => 'librarian']);
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->deleteJson("/api/books/{$book->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    public function test_member_cannot_update_book()
    {
        $user = User::factory()->create(['role' => 'member']);
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->putJson("/api/books/{$book->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(403);
    }

    public function test_member_cannot_delete_book()
    {
        $user = User::factory()->create(['role' => 'member']);
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->deleteJson("/api/books/{$book->id}");

        $response->assertStatus(403);
    }
}
