<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct(
        private readonly CommentService $commentService,
    ) {}

    public function store(StoreCommentRequest $request, Post $post): JsonResponse
    {
        $comment = $this->commentService->create(
            $post,
            $request->user(),
            $request->validated('content')
        );

        return response()->json(['comment' => new CommentResource($comment)], 201);
    }

    public function destroy(Request $request, Comment $comment): JsonResponse
    {
        $this->commentService->delete($comment, $request->user());

        return response()->json(['message' => 'Comentário excluído com sucesso.']);
    }
}
