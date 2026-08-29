<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;

/**
 * Read-only, public-facing book browsing/search/filtering for Users.
 * (Admin CRUD lives separately in App\Http\Controllers\Admin\BookController.)
 */
class BookController extends Controller
{
    public function index(Request $request)
    {
        $books = Book::query()
            ->with('category')
            ->when($request->query('search'), function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('author', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->query('category_id'), fn ($q, $categoryId) => $q->where('category_id', $categoryId))
            ->paginate(20);

        return response()->json($books);
    }

    public function show(Book $book)
    {
        return response()->json($book->load('category'));
    }
}
