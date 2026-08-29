<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin wrapper around the OpenAI HTTP API.
 * Handles chat completions (for the chatbot) and embeddings
 * (for the recommendation engine).
 */
class OpenAIService
{
    protected string $apiKey;
    protected string $chatModel;
    protected string $embeddingModel;
    //protected string $baseUrl = 'https://api.openai.com/v1';
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/openai';

    public function __construct()
    {
        $this->apiKey = (string) config('services.openai.key');
        $this->chatModel = (string) config('services.openai.chat_model', 'gpt-4o-mini');
        $this->embeddingModel = (string) config('services.openai.embedding_model', 'text-embedding-3-small');
    }

    /**
     * Send a chat completion request.
     *
     * @param array $messages Array of ['role' => 'system'|'user'|'assistant', 'content' => string]
     */
    public function chat(array $messages, float $temperature = 0.3): string
    {
        $response = Http::withToken($this->apiKey)
            ->timeout(30)
            ->post("{$this->baseUrl}/chat/completions", [
                'model' => $this->chatModel,
                'messages' => $messages,
                'temperature' => $temperature,
            ]);

        if ($response->failed()) {
            Log::error('OpenAI chat request failed', ['body' => $response->body()]);
            throw new \RuntimeException('The AI service is currently unavailable. Please try again later.');
        }

        return $response->json('choices.0.message.content') ?? '';
    }

    /**
     * Generate an embedding vector for a piece of text.
     *
     * @return float[]
     */
    public function embed(string $text): array
{
    $model = str_replace('models/', '', $this->embeddingModel);

    $response = Http::timeout(30)
        ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:embedContent?key={$this->apiKey}", [
            'content' => [
                'parts' => [
                    ['text' => $text],
                ],
            ],
        ]);

    if ($response->failed()) {
        Log::error('OpenAI embedding request failed', ['body' => $response->body()]);
        throw new \RuntimeException('The AI service is currently unavailable. Please try again later.');
    }

    return $response->json('embedding.values') ?? [];
}
}
