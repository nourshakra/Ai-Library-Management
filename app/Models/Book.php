<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'description',
        'category_id',
        'isbn',
        'publication_date',
        'available_copies',
        'cover_path',
        'metadata',
        'embedding',
    ];

    protected $casts = [
        'metadata' => 'array',
        'publication_date' => 'date',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Text blob used to generate the book's embedding.
     */
    public function embeddingText(): string
    {
        return collect([
            $this->title,
            'by ' . $this->author,
            $this->description,
            'Category: ' . ($this->category?->name ?? ''),
        ])->filter()->implode('. ');
    }
}
