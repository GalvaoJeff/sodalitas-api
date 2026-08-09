<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\User;
use App\Services\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __construct(
        private readonly PostService $postService,
    ) {}

    /**
     * Feed principal: posts do usuário logado + de quem ele segue.
     */
    public function index(Request $request): JsonResponse
    {
        $posts = $this->postService->feedFor($request->user());

        return response()->json([
            'posts' => PostResource::collection($posts),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'has_more' => $posts->hasMorePages(),
            ],
        ]);
    }

    /**
     * Posts de um usuário específico, para a grade do perfil.
     */
    public function byUsername(Request $request, string $username): JsonResponse
    {
        $profileUser = User::where('username', $username)->firstOrFail();

        $posts = $this->postService->forUser($profileUser, $request->user());

        return response()->json([
            'posts' => PostResource::collection($posts),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'has_more' => $posts->hasMorePages(),
            ],
        ]);
    }

    public function store(StorePostRequest $request): JsonResponse
    {
        $post = $this->postService->create(
            $request->user(),
            $request->safe()->only('caption'),
            $request->file('media', [])
        );

        return response()->json(['post' => new PostResource($post)], 201);
    }

    public function show(Post $post): JsonResponse
    {
        $post->load(['user', 'media', 'comments.user'])
            ->loadCount(['comments', 'likes']);

        return response()->json(['post' => new PostResource($post)]);
    }

    public function destroy(Request $request, Post $post): JsonResponse
    {
        $this->postService->delete($post, $request->user());

        return response()->json(['message' => 'Post excluído com sucesso.']);
    }
}
