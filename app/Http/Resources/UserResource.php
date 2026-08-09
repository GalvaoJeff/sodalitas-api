<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'bio' => $this->bio,
            'avatar_url' => $this->avatar_url,
            'birthdate' => $this->birthdate?->format('Y-m-d'),
            'location' => $this->location,
            'profession' => $this->profession,
            'education' => $this->education,
            'phone' => $this->phone,
            'show_phone' => $this->when(
                array_key_exists('own_profile', $this->resource->getAttributes())
                    && $this->resource->getAttributes()['own_profile'],
                fn () => (bool) $this->show_phone
            ),
            'hobbies' => $this->hobbies,
            'posts_count' => $this->whenCounted('posts'),
            'followers_count' => $this->whenCounted('followers'),
            'following_count' => $this->whenCounted('following'),
            'own_profile' => $this->when(
                array_key_exists('own_profile', $this->resource->getAttributes()),
                fn () => (bool) $this->resource->getAttributes()['own_profile']
            ),
            // Setado manualmente pelo Controller/Service via setAttribute()
            // apenas quando faz sentido no contexto (ex: perfil de outro
            // usuário). Evita cálculo desnecessário em listas.
            'is_followed_by_me' => $this->when(
                array_key_exists('is_followed_by_me', $this->resource->getAttributes()),
                fn () => (bool) $this->resource->getAttributes()['is_followed_by_me']
            ),
            'created_at' => $this->created_at,
        ];
    }
}
