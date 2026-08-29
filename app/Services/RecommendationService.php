<?php

namespace App\Services;

use App\Models\Book;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Implements the "AI Matching Process" from the spec:
 * 1. Build/update embeddings for the user profile and each book.
 * 2. Compute cosine similarity between the user vector and each book vector.
 * 3. Convert similarity into a 0-100% match score.
 * 4. Return books sorted by relevance.
 */
class RecommendationService
{
    public function __construct(protected OpenAIService $openAI)
    {
    }

    /**
     * Ensure the user's profile embedding is up to date and return it.
     */
    public function userEmbedding(User $user): array
    {
        if (empty($user->profile_embedding)) {
            $vector = $this->openAI->embed($user->profileText());
            $user->profile_embedding = json_encode($vector);
            $user->save();

            return $vector;
        }

        return json_decode($user->profile_embedding, true) ?? [];
    }

    /**
     * Ensure a book's embedding is up to date and return it.
     */
    public function bookEmbedding(Book $book): array
    {
        if (empty($book->embedding)) {
            $vector = $this->openAI->embed($book->embeddingText());
            $book->embedding = json_encode($vector);
            $book->save();

            return $vector;
        }

        return json_decode($book->embedding, true) ?? [];
    }

    /**
     * Recompute a book's embedding (call this after a book is created/updated).
     */
    public function refreshBookEmbedding(Book $book): void
    {
        $vector = $this->openAI->embed($book->embeddingText());
        $book->embedding = json_encode($vector);
        $book->save();
    }

    /**
     * Recompute a user's profile embedding (call this after profile update).
     */
    public function refreshUserEmbedding(User $user): void
    {
        $vector = $this->openAI->embed($user->profileText());
        $user->profile_embedding = json_encode($vector);
        $user->save();
    }

    /**
     * Return all books with a "match" percentage, sorted best-first.
     */
    public function recommendationsFor(User $user): Collection
    {
        $userVector = $this->userEmbedding($user);

        return Book::with('category')->get()->map(function (Book $book) use ($userVector) {
            $bookVector = $this->bookEmbedding($book);
            $similarity = $this->cosineSimilarity($userVector, $bookVector);

            return [
                'book' => $book,
                'match' => $this->similarityToPercentage($similarity),
            ];
        })->sortByDesc('match')->values();
    }

    protected function cosineSimilarity(array $a, array $b): float
    {
        if (empty($a) || empty($b) || count($a) !== count($b)) {
            return 0.0;
        }

        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        foreach ($a as $i => $valueA) {
            $valueB = $b[$i];
            $dot += $valueA * $valueB;
            $normA += $valueA ** 2;
            $normB += $valueB ** 2;
        }

        if ($normA == 0.0 || $normB == 0.0) {
            return 0.0;
        }

        return $dot / (sqrt($normA) * sqrt($normB));
    }

    /**
     * Cosine similarity ranges roughly from -1 to 1; for text embeddings
     * it is typically 0..1. Clamp and scale to a 0-100 percentage.
     */
    protected function similarityToPercentage(float $similarity): int
    {
        $clamped = max(0.0, min(1.0, $similarity));

        return (int) round($clamped * 100);
    }
}
