<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['admin', 'user'])->default('user');

            // Profile fields used by the recommendation engine
            $table->json('interests')->nullable();
            $table->json('favorite_topics')->nullable();
            $table->json('preferred_categories')->nullable();
            $table->json('skills')->nullable();
            $table->text('learning_goals')->nullable();

            // Cached embedding vector for the user's profile (JSON array of floats)
            $table->longText('profile_embedding')->nullable();

            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
