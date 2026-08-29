<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('author');
            $table->text('description')->nullable();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('isbn')->unique()->nullable();
            $table->date('publication_date')->nullable();
            $table->unsignedInteger('available_copies')->default(0);
            $table->string('cover_path')->nullable();
            $table->json('metadata')->nullable();

            // Cached embedding vector for the book (title+description+category)
            $table->longText('embedding')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
