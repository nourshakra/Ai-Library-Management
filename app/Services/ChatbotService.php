<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;

/**
 * Role-aware AI chatbot.
 *
 * IMPORTANT (per spec section 6 & 7): the AI is never given direct DB access
 * and never decides what it is allowed to see. This service:
 *   1. Looks at the authenticated user's role FIRST.
 *   2. Builds a restricted "context" containing ONLY the data that role
 *      is allowed to access.
 *   3. Sends that context + the user's question to OpenAI.
 *   4. The model answers using ONLY the provided context.
 *
 * This enforces: Authentication -> Authorization -> Role -> Allowed Data -> AI -> Response
 */
class ChatbotService
{
    public function __construct(
        protected OpenAIService $openAI,
        protected RecommendationService $recommendations,
    ) {
    }

    public function ask(User $user, string $question): array
    {
        $context = $user->isAdmin()
            ? $this->buildAdminContext()
            : $this->buildUserContext($user);

        $systemPrompt = $this->systemPrompt($user);

        $answer = $this->openAI->chat([
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'system', 'content' => "Authorized data context (JSON):\n" . json_encode($context)],
            ['role' => 'user', 'content' => $question],
        ]);

        return [
            'answer' => $answer,
            'role' => $user->role,
        ];
    }

    protected function systemPrompt(User $user): string
    {
        if ($user->isAdmin()) {
            return "You are the library management assistant for an ADMIN user. "
                . "Answer using ONLY the JSON context provided (library statistics, categories, books, user counts). "
                . "Never invent numbers. If the context does not contain the answer, say you don't have that information. "
                . "You may discuss any book, category, or aggregate statistic in the context.";
        }

        return "You are the library assistant for a regular USER (id={$user->id}). "
            . "Answer using ONLY the JSON context provided (public book catalog, categories, and this user's own profile/recommendations). "
            . "You must NEVER reveal information about other users, admin statistics, or any user list — "
            . "if asked for such things, politely explain that this requires admin privileges and refuse. "
            . "You may recommend books, explain a book, or compare books using the context.";
    }

    /**
     * Admin-only aggregate data. Still no raw PII beyond counts, and no
     * password/hash fields are ever included.
     */
    protected function buildAdminContext(): array
    {
        $booksByCategory = Category::withCount('books')->get()
            ->map(fn (Category $c) => ['category' => $c->name, 'book_count' => $c->books_count]);

        return [
            'total_books' => Book::count(),
            'total_users' => User::count(),
            'books_by_category' => $booksByCategory,
            'low_availability_books' => Book::where('available_copies', '<=', 2)
                ->get(['id', 'title', 'author', 'available_copies']),
            'all_books' => Book::with('category')->get()->map(fn (Book $b) => [
                'id' => $b->id,
                'title' => $b->title,
                'author' => $b->author,
                'category' => $b->category?->name,
                'available_copies' => $b->available_copies,
            ]),
        ];
    }

    /**
     * User-scoped data only: public catalog + the user's own profile
     * and personalized recommendations. No other user's data, ever.
     */
    protected function buildUserContext(User $user): array
    {
        $recommendations = $this->recommendations->recommendationsFor($user)->take(10)
            ->map(fn ($r) => [
                'title' => $r['book']->title,
                'author' => $r['book']->author,
                'category' => $r['book']->category?->name,
                'match_percent' => $r['match'],
            ]);

        return [
            'categories' => Category::pluck('name'),
            'catalog' => Book::with('category')->get()->map(fn (Book $b) => [
                'id' => $b->id,
                'title' => $b->title,
                'author' => $b->author,
                'description' => $b->description,
                'category' => $b->category?->name,
                'available_copies' => $b->available_copies,
            ]),
            'my_profile' => [
                'interests' => $user->interests,
                'favorite_topics' => $user->favorite_topics,
                'preferred_categories' => $user->preferred_categories,
                'skills' => $user->skills,
                'learning_goals' => $user->learning_goals,
            ],
            'my_recommendations' => $recommendations,
        ];
    }
}
