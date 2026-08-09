<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'caption' => $this->caption,
            'user' => new UserResource($this->whenLoaded('user')),
            'media' => MediaResource::collection($this->whenLoaded('media')),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
            'comments_count' => $this->whenCounted('comments'),
            'likes_count' => $this->whenCounted('likes'),
            // Setado manualmente no Service (ver PostService) verificando
            // se o usuário autenticado curtiu este post especificamente.
            'liked_by_me' => $this->when(
                array_key_exists('liked_by_me', $this->resource->getAttributes()),
                fn () => (bool) $this->resource->getAttributes()['liked_by_me']
            ),
            'created_at' => $this->created_at,
        ];
    }
}
