<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Services\RecommendationService;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function __construct(private RecommendationService $recommendationService) {}

    public function index()
    {
        return Book::with('category')->get();
    }

    public function store(StoreBookRequest $request)
    {
        $book = Book::create($request->validated());
        $this->recommendationService->refreshBookEmbedding($book);
        return response()->json($book->load('category'), 201);
    }

    public function show(Book $book)
    {
        return $book->load('category');
    }

    public function update(UpdateBookRequest $request, Book $book)
    {
        $book->update($request->validated());
        $this->recommendationService->refreshBookEmbedding($book);
        return response()->json($book->load('category'));
    }

    public function destroy(Book $book)
    {
        $book->delete();
        return response()->json(null, 204);
    }
}
