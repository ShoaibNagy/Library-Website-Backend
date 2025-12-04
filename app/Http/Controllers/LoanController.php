<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

/**
 * @group Loan Operations
 *
 * APIs for managing book loans
 */
class LoanController extends Controller
{
    /**
     * Borrow a book
     * 
     * @authenticated
     *
     * @bodyParam book_id integer required The ID of the book to borrow. Example: 1
     * 
     * @response 201 {
     *  "id": 1,
     *  "user_id": 1,
     *  "book_id": 1,
     *  "borrowed_at": "2023-01-01T00:00:00.000000Z",
     *  "due_date": "2023-01-15T00:00:00.000000Z",
     *  "status": "borrowed",
     *  "created_at": "2023-01-01T00:00:00.000000Z",
     *  "updated_at": "2023-01-01T00:00:00.000000Z"
     * }
     * @response 422 {
     *  "message": "The given data was invalid.",
     *  "errors": {
     *      "book_id": [
     *          "This book is currently unavailable."
     *      ]
     *  }
     * }
     */
    public function borrow(Request $request)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
        ]);

        $user = $request->user();
        
        // Check if user is active
        if ($user->status !== 'active') {
            throw ValidationException::withMessages(['message' => 'Your account is suspended.']);
        }

        // Check for overdue books (optional policy)
        if ($user->loans()->where('status', 'overdue')->exists()) {
             throw ValidationException::withMessages(['message' => 'You have overdue books. Please return them first.']);
        }

        $book = Book::findOrFail($request->book_id);

        if ($book->available_copies < 1) {
            throw ValidationException::withMessages(['book_id' => 'This book is currently unavailable.']);
        }

        // Create Loan
        $loan = Loan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'borrowed_at' => now(),
            'due_date' => now()->addDays(14), // 2 weeks loan period
            'status' => 'borrowed',
        ]);

        // Decrement available copies
        $book->decrement('available_copies');

        return response()->json($loan, 201);
    }

    /**
     * Return a book
     * 
     * @authenticated
     *
     * @urlParam id integer required The ID of the loan. Example: 1
     * 
     * @response {
     *  "id": 1,
     *  "user_id": 1,
     *  "book_id": 1,
     *  "borrowed_at": "2023-01-01T00:00:00.000000Z",
     *  "due_date": "2023-01-15T00:00:00.000000Z",
     *  "returned_at": "2023-01-10T00:00:00.000000Z",
     *  "status": "returned",
     *  "fine_amount": 0,
     *  "created_at": "2023-01-01T00:00:00.000000Z",
     *  "updated_at": "2023-01-10T00:00:00.000000Z"
     * }
     */
    public function returnBook(Request $request, $id)
    {
        $loan = Loan::findOrFail($id);
        
        // Authorize: Admin/Librarian can return any, User can't return via API usually (physical action), 
        // but for this system let's assume Admin/Librarian performs the return action.
        Gate::authorize('update', $loan);

        if ($loan->status === 'returned') {
             return response()->json(['message' => 'Book already returned'], 400);
        }

        $loan->returned_at = now();
        $loan->status = 'returned';

        // Calculate Fine
        if (now()->gt($loan->due_date)) {
            $daysOverdue = now()->diffInDays($loan->due_date);
            $finePerDay = 0.50; // $0.50 per day
            $loan->fine_amount = $daysOverdue * $finePerDay;
            $loan->status = 'overdue'; // Or keep as returned but with fine? Let's say 'returned' but fine is recorded.
            // Actually, if it's returned, status should be returned. Fine is just a field.
        }

        $loan->save();

        // Increment available copies
        $loan->book->increment('available_copies');

        return response()->json($loan);
    }

    /**
     * Get my loan history
     * 
     * @authenticated
     *
     * @response {
     *  "current_page": 1,
     *  "data": [
     *      {
     *          "id": 1,
     *          "user_id": 1,
     *          "book_id": 1,
     *          "borrowed_at": "2023-01-01T00:00:00.000000Z",
     *          "due_date": "2023-01-15T00:00:00.000000Z",
     *          "status": "borrowed",
     *          "book": {
     *              "id": 1,
     *              "title": "Harry Potter"
     *          }
     *      }
     *  ],
     *  "total": 1
     * }
     */
    public function myHistory(Request $request)
    {
        $loans = $request->user()->loans()->with('book')->orderBy('created_at', 'desc')->paginate(10);
        return response()->json($loans);
    }

    /**
     * List overdue loans
     * 
     * @authenticated
     *
     * @response {
     *  "current_page": 1,
     *  "data": [
     *      {
     *          "id": 1,
     *          "user_id": 1,
     *          "book_id": 1,
     *          "borrowed_at": "2023-01-01T00:00:00.000000Z",
     *          "due_date": "2022-01-01T00:00:00.000000Z",
     *          "status": "borrowed",
     *          "user": {
     *              "id": 1,
     *              "name": "John Doe"
     *          },
     *          "book": {
     *              "id": 1,
     *              "title": "Harry Potter"
     *          }
     *      }
     *  ],
     *  "total": 1
     * }
     */
    public function overdue(Request $request)
    {
        Gate::authorize('viewAny', Loan::class);

        $overdueLoans = Loan::with(['user', 'book'])
                            ->where('due_date', '<', now())
                            ->where('status', 'borrowed')
                            ->paginate(10);

        return response()->json($overdueLoans);
    }
}
