<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Services\Ai\AiChatService;
use Illuminate\Http\Request;

class AiChatController extends Controller
{
    public function store(Request $request, AiChatService $chatService)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:600'],
            'history' => ['nullable', 'array', 'max:8'],
            'history.*.role' => ['required_with:history', 'string', 'in:user,assistant'],
            'history.*.content' => ['required_with:history', 'string', 'max:600'],
            'page' => ['nullable', 'array'],
            'page.url' => ['nullable', 'string', 'max:1000'],
            'page.path' => ['nullable', 'string', 'max:500'],
            'page.title' => ['nullable', 'string', 'max:160'],
        ], [
            'message.required' => 'Vui lòng nhập nội dung cần tư vấn.',
            'message.max' => 'Nội dung tư vấn tối đa 600 ký tự.',
        ]);

        $page = $data['page'] ?? [];
        $referer = $request->headers->get('referer');

        if ($referer && empty($page['url'])) {
            $page['url'] = $referer;
        }

        if ($referer && empty($page['path'])) {
            $refererPath = parse_url($referer, PHP_URL_PATH);
            if (is_string($refererPath) && $refererPath !== '') {
                $page['path'] = $refererPath;
            }
        }

        return response()->json($chatService->answer(
            $data['message'],
            $data['history'] ?? [],
            $page,
            $request->user()
        ));
    }
}
