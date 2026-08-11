<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Representa o carrossel de stories: um usuário e a lista de suas
 * stories ativas, na ordem esperada pelo componente do frontend.
 */
class StoryGroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'user' => [
                'id' => $this['user']->id,
                'name' => $this['user']->name,
                'username' => $this['user']->username,
                'avatar_url' => $this['user']->avatar_url,
            ],
            'stories' => StoryResource::collection($this['stories']),
        ];
    }
}
