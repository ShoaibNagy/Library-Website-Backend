<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Book;
use App\Models\Author;
use App\Models\Edition;
use App\Models\Copy;
use App\Models\Loan;
use Carbon\Carbon;

uses(RefreshDatabase::class);

test('checkout requires authentication', function () {
    // create copy
    $book = Book::factory()->create();
    $author = Author::factory()->create();
    $edition = Edition::factory()->create(['book_id' => $book->id, 'author_id' => $author->id]);
    $copy = Copy::factory()->create(['edition_id' => $edition->id, 'status' => 'available']);

    $response = $this->postJson('/api/v1/loans/checkout', [
        'user_id' => 1,
        'copy_id' => $copy->id,
        'due_date' => Carbon::now()->addDays(14)->toDateString(),
    ]);

    $response->assertStatus(401);
});

test('patron can checkout available copy and copy becomes borrowed', function () {
    $user = User::factory()->create(['api_token' => 'test-token', 'role' => 'patron']);

    $book = Book::factory()->create();
    $author = Author::factory()->create();
    $edition = Edition::factory()->create(['book_id' => $book->id, 'author_id' => $author->id]);
    $copy = Copy::factory()->create(['edition_id' => $edition->id, 'status' => 'available']);

    $response = $this->postJson('/api/v1/loans/checkout', [
        'user_id' => $user->id,
        'copy_id' => $copy->id,
        'due_date' => Carbon::now()->addDays(14)->toDateString(),
    ], ['X-API-TOKEN' => $user->api_token]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('loans', [
        'user_id' => $user->id,
        'copy_id' => $copy->id,
        'status' => 'active',
    ]);

    $this->assertDatabaseHas('copies', [
        'id' => $copy->id,
        'status' => 'borrowed',
    ]);
});

test('cannot checkout an already borrowed copy', function () {
    $user1 = User::factory()->create(['api_token' => 'token-1', 'role' => 'patron']);
    $user2 = User::factory()->create(['api_token' => 'token-2', 'role' => 'patron']);

    $book = Book::factory()->create();
    $author = Author::factory()->create();
    $edition = Edition::factory()->create(['book_id' => $book->id, 'author_id' => $author->id]);
    $copy = Copy::factory()->create(['edition_id' => $edition->id, 'status' => 'available']);

    // First checkout succeeds
    $this->postJson('/api/v1/loans/checkout', [
        'user_id' => $user1->id,
        'copy_id' => $copy->id,
        'due_date' => Carbon::now()->addDays(14)->toDateString(),
    ], ['X-API-TOKEN' => $user1->api_token])->assertStatus(201);

    // Second checkout should fail with conflict
    $this->postJson('/api/v1/loans/checkout', [
        'user_id' => $user2->id,
        'copy_id' => $copy->id,
        'due_date' => Carbon::now()->addDays(14)->toDateString(),
    ], ['X-API-TOKEN' => $user2->api_token])->assertStatus(409);
});

test('return updates loan and copy status', function () {
    $user = User::factory()->create(['api_token' => 'return-token', 'role' => 'patron']);

    $book = Book::factory()->create();
    $author = Author::factory()->create();
    $edition = Edition::factory()->create(['book_id' => $book->id, 'author_id' => $author->id]);
    $copy = Copy::factory()->create(['edition_id' => $edition->id, 'status' => 'available']);

    // Checkout
    $res = $this->postJson('/api/v1/loans/checkout', [
        'user_id' => $user->id,
        'copy_id' => $copy->id,
        'due_date' => Carbon::now()->addDays(14)->toDateString(),
    ], ['X-API-TOKEN' => $user->api_token]);

    $res->assertStatus(201);
    $loanId = $res->json('id');

    // Return
    $this->postJson("/api/v1/loans/{$loanId}/return", [], ['X-API-TOKEN' => $user->api_token])
        ->assertStatus(200);

    $this->assertDatabaseHas('loans', [
        'id' => $loanId,
        'status' => 'returned',
    ]);

    $this->assertDatabaseHas('copies', [
        'id' => $copy->id,
        'status' => 'available',
    ]);
});

test('renew enforces max loan length', function () {
    $user = User::factory()->create(['api_token' => 'renew-token', 'role' => 'patron']);

    $book = Book::factory()->create();
    $author = Author::factory()->create();
    $edition = Edition::factory()->create(['book_id' => $book->id, 'author_id' => $author->id]);
    $copy = Copy::factory()->create(['edition_id' => $edition->id, 'status' => 'available']);

    $res = $this->postJson('/api/v1/loans/checkout', [
        'user_id' => $user->id,
        'copy_id' => $copy->id,
        'due_date' => Carbon::now()->addDays(14)->toDateString(),
    ], ['X-API-TOKEN' => $user->api_token]);

    $res->assertStatus(201);
    $loanId = $res->json('id');

    // Try to renew beyond allowed max (e.g., 90 days)
    $this->postJson("/api/v1/loans/{$loanId}/renew", [
        'due_date' => Carbon::now()->addDays(90)->toDateString(),
    ], ['X-API-TOKEN' => $user->api_token])->assertStatus(422);
});
