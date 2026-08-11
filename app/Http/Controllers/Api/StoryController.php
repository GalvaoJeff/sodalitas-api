<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStoryRequest;
use App\Http\Resources\StoryGroupResource;
use App\Http\Resources\StoryResource;
use App\Models\Story;
use App\Models\User;
use App\Services\StoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoryController extends Controller
{
    public function __construct(
        private readonly StoryService $storyService,
    ) {}

    /**
     * Carrossel do feed: grupos de stories ativas do usuário logado
     * e de quem ele segue.
     */
    public function index(Request $request): JsonResponse
    {
        $groups = $this->storyService->activeForFeed($request->user());

        return response()->json([
            'story_groups' => StoryGroupResource::collection($groups),
        ]);
    }

    /**
     * Stories ativas de um usuário específico (ao abrir o visualizador
     * a partir do avatar de alguém).
     */
    public function byUsername(string $username): JsonResponse
    {
        $profileUser = User::where('username', $username)->firstOrFail();

        $stories = $this->storyService->forUser($profileUser);

        return response()->json([
            'stories' => StoryResource::collection($stories),
        ]);
    }

    public function store(StoreStoryRequest $request): JsonResponse
    {
        $story = $this->storyService->create($request->user(), $request->file('media'));

        return response()->json(['story' => new StoryResource($story)], 201);
    }

    public function destroy(Request $request, Story $story): JsonResponse
    {
        $this->storyService->delete($story, $request->user());

        return response()->json(['message' => 'Story excluída com sucesso.']);
    }
}
