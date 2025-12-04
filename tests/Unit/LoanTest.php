<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanTest extends TestCase
{
    use RefreshDatabase;

    public function test_loan_belongs_to_user()
    {
        $user = User::factory()->create();
        $loan = Loan::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $loan->user);
        $this->assertEquals($user->id, $loan->user->id);
    }

    public function test_loan_belongs_to_book()
    {
        $book = Book::factory()->create();
        $loan = Loan::factory()->create(['book_id' => $book->id]);

        $this->assertInstanceOf(Book::class, $loan->book);
        $this->assertEquals($book->id, $loan->book->id);
    }

    public function test_loan_defaults_to_borrowed_status()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        
        $loan = Loan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'borrowed_at' => now(),
            'due_date' => now()->addDays(14),
        ]);

        $loan->refresh();
        $this->assertEquals('borrowed', $loan->status);
    }

    public function test_loan_scope_overdue()
    {
        Loan::factory()->create([
            'due_date' => now()->subDay(),
            'status' => 'borrowed',
        ]);

        Loan::factory()->create([
            'due_date' => now()->addDay(),
            'status' => 'borrowed',
        ]);

        $this->assertCount(1, Loan::overdue()->get());
    }

    public function test_loan_scope_current()
    {
        Loan::factory()->create(['status' => 'borrowed']);
        Loan::factory()->create(['status' => 'returned']);

        $this->assertCount(1, Loan::current()->get());
    }
}
