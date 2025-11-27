<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Copy;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LoansController extends Controller
{
    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'copy_id' => ['required', 'exists:copies,id'],
            'user_id' => ['required', 'exists:users,id'],
            'due_date' => ['required', 'date', 'after:today'],
        ]);

        // Business rules
        $maxActiveLoans = 5; // limit per user
        $maxLoanLengthDays = 60; // max days until due date

        $due = \Carbon\Carbon::parse($validated['due_date']);
        $daysUntilDue = now()->diffInDays($due);
        if ($daysUntilDue > $maxLoanLengthDays) {
            return response()->json(['message' => "Due date cannot be more than {$maxLoanLengthDays} days from now"], 422);
        }

        // Use a transaction and lock the copy row to prevent double checkout
        $loan = DB::transaction(function () use ($validated, $maxActiveLoans) {
            // Lock the copy row for update
            $copy = Copy::where('id', $validated['copy_id'])->lockForUpdate()->first();
            if (! $copy) {
                abort(404, 'Copy not found');
            }

            if (! $copy->isAvailable()) {
                abort(409, 'Copy is not available');
            }

            // Ensure user does not exceed max active loans
            $activeCount = Loan::where('user_id', $validated['user_id'])->where('status', 'active')->lockForUpdate()->count();
            if ($activeCount >= $maxActiveLoans) {
                abort(422, 'User has reached maximum number of active loans');
            }

            // Create loan and update copy status atomically
            $loan = Loan::create([
                'user_id' => $validated['user_id'],
                'copy_id' => $validated['copy_id'],
                'checkout_date' => now(),
                'due_date' => $validated['due_date'],
                'status' => 'active',
            ]);

            $copy->update(['status' => 'borrowed']);

            return $loan;
        });

        return response()->json($loan, 201);
    }

    public function return(Request $request, $loanId)
    {
        $loan = Loan::findOrFail($loanId);

        // Use transaction to ensure loan and copy update atomically
        $updated = DB::transaction(function () use ($loan) {
            // Lock loan row
            $lockedLoan = Loan::where('id', $loan->id)->lockForUpdate()->first();
            if ($lockedLoan->status !== 'active') {
                abort(409, 'Loan is not active');
            }

            $lockedLoan->update([
                'return_date' => now(),
                'status' => 'returned',
            ]);

            // Lock the copy row and mark available
            $copy = Copy::where('id', $lockedLoan->copy_id)->lockForUpdate()->first();
            if ($copy) {
                $copy->update(['status' => 'available']);
            }

            return $lockedLoan;
        });

        return response()->json($updated);
    }

    public function renew(Request $request, $loanId)
    {
        $loan = Loan::findOrFail($loanId);

        $validated = $request->validate([
            'due_date' => ['required', 'date', 'after:today'],
        ]);

        $maxLoanLengthDays = 60;
        $newDue = \Carbon\Carbon::parse($validated['due_date']);
        $daysUntilNewDue = now()->diffInDays($newDue);
        if ($daysUntilNewDue > $maxLoanLengthDays) {
            return response()->json(['message' => "Due date cannot be more than {$maxLoanLengthDays} days from now"], 422);
        }

        // Lock loan row during renew
        $updated = DB::transaction(function () use ($loan, $validated) {
            $lockedLoan = Loan::where('id', $loan->id)->lockForUpdate()->first();
            if ($lockedLoan->status !== 'active') {
                abort(409, 'Loan is not active');
            }

            $lockedLoan->update(['due_date' => $validated['due_date']]);
            return $lockedLoan;
        });

        return response()->json($updated);
    }

    public function userLoans($userId)
    {
        $loans = Loan::with('copy.edition.book')
            ->where('user_id', $userId)
            ->orderBy('checkout_date', 'desc')
            ->get();

        return response()->json($loans);
    }
}
