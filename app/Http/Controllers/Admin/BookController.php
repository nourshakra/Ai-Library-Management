<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Services\RecommendationService;
use Illuminate\Support\Facades\Storage;

class BookController extends Controller
{
    public function __construct(protected RecommendationService $recommendations)
    {
    }

    public function index()
    {
        return response()->json(Book::with('category')->paginate(20));
    }

    public function show(Book $book)
    {
        return response()->json($book->load('category'));
    }

    public function store(StoreBookRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('cover')) {
            $data['cover_path'] = $request->file('cover')->store('covers', 'public');
        }

        $book = Book::create($data);

        // Generate the embedding now so the book is immediately searchable/recommendable.
        $this->recommendations->refreshBookEmbedding($book);

        return response()->json($book->load('category'), 201);
    }

    public function update(UpdateBookRequest $request, Book $book)
    {
        $data = $request->validated();

        if ($request->hasFile('cover')) {
            if ($book->cover_path) {
                Storage::disk('public')->delete($book->cover_path);
            }
            $data['cover_path'] = $request->file('cover')->store('covers', 'public');
        }

        $book->update($data);

        // Title/description/category may have changed -> embedding must be refreshed.
        $this->recommendations->refreshBookEmbedding($book);

        return response()->json($book->load('category'));
    }

    public function destroy(Book $book)
    {
        if ($book->cover_path) {
            Storage::disk('public')->delete($book->cover_path);
        }

        $book->delete();

        return response()->json(['message' => 'Book deleted.']);
    }
}
