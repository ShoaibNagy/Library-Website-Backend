<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->query('query');
        $perPage = (int) $request->query('per_page', 15);

        $qb = Book::query();

        if ($query) {
            $qb->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('isbn', 'like', "%{$query}%");
            });
        }

        $books = $qb->orderBy('title')->paginate($perPage);

        return response()->json($books);
    }

    public function show($id)
    {
        $book = Book::find($id);

        if (! $book) {
            return response()->json(['message' => 'Not Found'], 404);
        }

        return response()->json($book);
    }
}
