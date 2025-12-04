<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * @group Book Management
 *
 * APIs for managing books
 */
class BookController extends Controller
{
    /**
     * List all books
     *
     * @queryParam search string Filter by title, isbn, or author name. Example: Harry Potter
     * @queryParam category_id integer Filter by category ID. Example: 1
     * @queryParam page integer The page number. Example: 1
     * 
     * @response {
     *  "current_page": 1,
     *  "data": [
     *      {
     *          "id": 1,
     *          "title": "Harry Potter",
     *          "isbn": "1234567890",
     *          "author": {
     *              "id": 1,
     *              "name": "J.K. Rowling"
     *          },
     *          "category": {
     *              "id": 1,
     *              "name": "Fantasy"
     *          },
     *          "total_copies": 10,
     *          "available_copies": 5
     *      }
     *  ],
     *  "total": 1
     * }
     */
    public function index(Request $request)
    {
        $query = Book::with(['author', 'category']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%")
                  ->orWhereHas('author', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        return response()->json($query->paginate(10));
    }

    /**
     * Get a specific book
     *
     * @urlParam id integer required The ID of the book. Example: 1
     * 
     * @response {
     *  "id": 1,
     *  "title": "Harry Potter",
     *  "isbn": "1234567890",
     *  "author": {
     *      "id": 1,
     *      "name": "J.K. Rowling"
     *  },
     *  "category": {
     *      "id": 1,
     *      "name": "Fantasy"
     *  },
     *  "total_copies": 10,
     *  "available_copies": 5
     * }
     */
    public function show($id)
    {
        $book = Book::with(['author', 'category'])->findOrFail($id);
        return response()->json($book);
    }

    /**
     * Create a new book
     * 
     * @authenticated
     *
     * @bodyParam title string required The title of the book. Example: Harry Potter
     * @bodyParam isbn string required The ISBN of the book. Example: 1234567890
     * @bodyParam author_id integer required The ID of the author. Example: 1
     * @bodyParam category_id integer required The ID of the category. Example: 1
     * @bodyParam total_copies integer required The total number of copies. Example: 10
     * @bodyParam available_copies integer required The number of available copies. Example: 10
     * @bodyParam publication_year integer The year of publication. Example: 2000
     * @bodyParam description string The description of the book. Example: A wizard boy...
     * 
     * @response 201 {
     *  "id": 1,
     *  "title": "Harry Potter",
     *  "isbn": "1234567890",
     *  "author_id": 1,
     *  "category_id": 1,
     *  "total_copies": 10,
     *  "available_copies": 10
     * }
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Book::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'isbn' => 'required|string|unique:books',
            'author_id' => 'required|exists:authors,id',
            'category_id' => 'required|exists:categories,id',
            'total_copies' => 'required|integer|min:1',
            'available_copies' => 'required|integer|min:0',
            'publication_year' => 'nullable|integer',
            'description' => 'nullable|string',
        ]);

        $book = Book::create($validated);

        return response()->json($book, 201);
    }

    /**
     * Update a book
     * 
     * @authenticated
     *
     * @urlParam id integer required The ID of the book. Example: 1
     * @bodyParam title string The title of the book. Example: Harry Potter
     * @bodyParam isbn string The ISBN of the book. Example: 1234567890
     * @bodyParam author_id integer The ID of the author. Example: 1
     * @bodyParam category_id integer The ID of the category. Example: 1
     * @bodyParam total_copies integer The total number of copies. Example: 10
     * @bodyParam available_copies integer The number of available copies. Example: 10
     * @bodyParam publication_year integer The year of publication. Example: 2000
     * @bodyParam description string The description of the book. Example: A wizard boy...
     * 
     * @response {
     *  "id": 1,
     *  "title": "Harry Potter",
     *  "isbn": "1234567890",
     *  "author_id": 1,
     *  "category_id": 1,
     *  "total_copies": 10,
     *  "available_copies": 10
     * }
     */
    public function update(Request $request, $id)
    {
        $book = Book::findOrFail($id);
        Gate::authorize('update', $book);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'isbn' => 'sometimes|string|unique:books,isbn,' . $book->id,
            'author_id' => 'sometimes|exists:authors,id',
            'category_id' => 'sometimes|exists:categories,id',
            'total_copies' => 'sometimes|integer|min:1',
            'available_copies' => 'sometimes|integer|min:0',
            'publication_year' => 'nullable|integer',
            'description' => 'nullable|string',
        ]);

        $book->update($validated);

        return response()->json($book);
    }

    /**
     * Delete a book
     * 
     * @authenticated
     *
     * @urlParam id integer required The ID of the book. Example: 1
     * 
     * @response {
     *  "message": "Book deleted successfully"
     * }
     */
    public function destroy($id)
    {
        $book = Book::findOrFail($id);
        Gate::authorize('delete', $book);

        $book->delete();

        return response()->json(['message' => 'Book deleted successfully']);
    }
}
