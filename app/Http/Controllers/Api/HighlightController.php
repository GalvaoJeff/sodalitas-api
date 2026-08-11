<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddHighlightStoryRequest;
use App\Http\Requests\StoreHighlightRequest;
use App\Http\Resources\HighlightResource;
use App\Models\Highlight;
use App\Models\HighlightStory;
use App\Models\Story;
use App\Models\User;
use App\Services\HighlightService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HighlightController extends Controller
{
    public function __construct(
        private readonly HighlightService $highlightService,
    ) {}

    /**
     * Destaques de um usuário, para exibir na tela de perfil.
     */
    public function byUsername(string $username): JsonResponse
    {
        $profileUser = User::where('username', $username)->firstOrFail();

        $highlights = $this->highlightService->listForUser($profileUser);

        return response()->json([
            'highlights' => HighlightResource::collection($highlights),
        ]);
    }

    public function store(StoreHighlightRequest $request): JsonResponse
    {
        $highlight = $this->highlightService->create(
            $request->user(),
            $request->validated('title')
        );

        return response()->json(['highlight' => new HighlightResource($highlight)], 201);
    }

    public function addStory(AddHighlightStoryRequest $request, Highlight $highlight): JsonResponse
    {
        $story = Story::findOrFail($request->validated('story_id'));

        $this->highlightService->addStory($highlight, $story, $request->user());

        return response()->json([
            'highlight' => new HighlightResource($highlight->fresh('items')),
        ], 201);
    }

    public function removeStory(Request $request, Highlight $highlight, HighlightStory $highlightStory): JsonResponse
    {
        $this->highlightService->removeStory($highlightStory, $request->user());

        return response()->json(['message' => 'Item removido do destaque.']);
    }

    public function destroy(Request $request, Highlight $highlight): JsonResponse
    {
        $this->highlightService->delete($highlight, $request->user());

        return response()->json(['message' => 'Destaque excluído com sucesso.']);
    }
}
