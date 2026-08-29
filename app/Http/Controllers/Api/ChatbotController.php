<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatLog;
use App\Services\ChatbotService;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function __construct(protected ChatbotService $chatbot)
    {
    }

    public function ask(Request $request)
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:1000'],
        ]);

        // Role-based context is resolved INSIDE ChatbotService, based on
        // $request->user()->role — the AI never chooses what it can see.
        $result = $this->chatbot->ask($request->user(), $data['question']);

        ChatLog::create([
            'user_id' => $request->user()->id,
            'question' => $data['question'],
            'answer' => $result['answer'],
        ]);

        return response()->json($result);
    }

    public function history(Request $request)
    {
        return response()->json(
            $request->user()->chatLogs()->latest()->paginate(20)
        );
    }
}
