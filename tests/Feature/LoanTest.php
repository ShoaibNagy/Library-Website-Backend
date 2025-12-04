<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoanTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_borrow_book()
    {
        $user = User::factory()->create(['role' => 'member', 'status' => 'active']);
        $book = Book::factory()->create(['available_copies' => 1]);

        $response = $this->actingAs($user)->postJson('/api/loans/borrow', [
            'book_id' => $book->id,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('loans', ['user_id' => $user->id, 'book_id' => $book->id]);
        $this->assertDatabaseHas('books', ['id' => $book->id, 'available_copies' => 0]);
    }

    public function test_member_cannot_borrow_unavailable_book()
    {
        $user = User::factory()->create(['role' => 'member', 'status' => 'active']);
        $book = Book::factory()->create(['available_copies' => 0]);

        $response = $this->actingAs($user)->postJson('/api/loans/borrow', [
            'book_id' => $book->id,
        ]);

        $response->assertStatus(422);
    }

    public function test_librarian_can_return_book()
    {
        $librarian = User::factory()->create(['role' => 'librarian']);
        $member = User::factory()->create(['role' => 'member']);
        $book = Book::factory()->create(['available_copies' => 0]);
        
        $loan = \App\Models\Loan::create([
            'user_id' => $member->id,
            'book_id' => $book->id,
            'borrowed_at' => now(),
            'due_date' => now()->addDays(14),
            'status' => 'borrowed',
        ]);

        $response = $this->actingAs($librarian)->postJson("/api/loans/{$loan->id}/return");

        $response->assertStatus(200);
        $this->assertDatabaseHas('loans', ['id' => $loan->id, 'status' => 'returned']);
        $this->assertDatabaseHas('books', ['id' => $book->id, 'available_copies' => 1]);
    }

    public function test_member_can_view_borrowing_history()
    {
        $user = User::factory()->create(['role' => 'member']);
        $loan = \App\Models\Loan::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson('/api/loans/my-history');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data');
    }

    public function test_librarian_can_view_overdue_loans()
    {
        $librarian = User::factory()->create(['role' => 'librarian']);
        \App\Models\Loan::factory()->create([
            'due_date' => now()->subDay(),
            'status' => 'borrowed',
        ]);

        $response = $this->actingAs($librarian)->getJson('/api/loans/overdue');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data');
    }
}
