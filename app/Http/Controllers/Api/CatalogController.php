<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDO;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->query('query');
        $perPage = (int) $request->query('per_page', 15);
        $authorFilter = $request->query('author');
        $yearFilter = $request->query('publication_year');
        $wantFacets = $request->boolean('facets');

        $qb = Book::query();

        // Apply author filter by joining editions->authors
        if ($authorFilter) {
            $qb->join('editions', 'editions.book_id', '=', 'books.id')
               ->join('authors', 'authors.id', '=', 'editions.author_id')
               ->where('authors.name', 'like', "%{$authorFilter}%");
        }

        if ($yearFilter) {
            $qb->where('publication_year', (int) $yearFilter);
        }

        if ($query) {
            // If MySQL with fulltext support, prefer MATCH...AGAINST for better relevance
            $isMysql = false;
            try {
                $isMysql = DB::getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql';
            } catch (\Throwable $e) {
                $isMysql = false;
            }

            if ($isMysql) {
                // safe to use boolean mode for partial matches
                $qb->whereRaw("MATCH(title, description) AGAINST (? IN BOOLEAN MODE)", [$query]);
            } else {
                $qb->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                      ->orWhere('isbn', 'like', "%{$query}%")
                      ->orWhere('description', 'like', "%{$query}%");
                });
            }
        }

        $books = $qb->orderBy('title')->paginate($perPage);

        $result = ['data' => $books];

        if ($wantFacets) {
            // publication_year facet
            $yearFacet = Book::select('publication_year', DB::raw('count(*) as count'))
                ->groupBy('publication_year')
                ->orderByDesc('count')
                ->get()
                ->map(function ($r) {
                    return ['value' => $r->publication_year, 'count' => (int) $r->count];
                });

            // author facet via editions->authors
            $authorFacet = DB::table('books')
                ->join('editions', 'editions.book_id', '=', 'books.id')
                ->join('authors', 'authors.id', '=', 'editions.author_id')
                ->select('authors.name as author', DB::raw('count(distinct books.id) as count'))
                ->groupBy('authors.name')
                ->orderByDesc('count')
                ->limit(20)
                ->get()
                ->map(function ($r) {
                    return ['value' => $r->author, 'count' => (int) $r->count];
                });

            $result['facets'] = ['publication_year' => $yearFacet, 'author' => $authorFacet];
        }

        return response()->json($result);
    }

    public function suggest(Request $request)
    {
        $q = $request->query('q');
        if (! $q) {
            return response()->json([], 200);
        }

        $isMysql = false;
        try {
            $isMysql = DB::getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql';
        } catch (\Throwable $e) {
            $isMysql = false;
        }

        $limit = (int) $request->query('limit', 10);

        $qb = Book::query()->select('id', 'title');
        if ($isMysql) {
            $qb->whereRaw("MATCH(title) AGAINST (? IN BOOLEAN MODE)", [$q]);
        } else {
            $qb->where('title', 'like', "%{$q}%");
        }

        $results = $qb->orderBy('title')->limit($limit)->get()->map(function ($b) {
            return ['id' => $b->id, 'title' => $b->title];
        });

        return response()->json($results);
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
