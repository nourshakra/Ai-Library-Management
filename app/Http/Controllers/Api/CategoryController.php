<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        return response()->json(Category::withCount('books')->get());
    }

    public function show(Category $category)
    {
        return response()->json($category->load('books.category'));
    }
}
