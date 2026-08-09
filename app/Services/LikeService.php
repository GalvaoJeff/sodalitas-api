<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;

class LikeService
{
    /**
     * Alterna a curtida: se já curtiu, remove; se não, cria.
     * Retorna o novo estado (true = curtido, false = descurtido).
     */
    public function toggle(Post $post, User $user): bool
    {
        $like = $post->likes()->where('user_id', $user->id)->first();

        if ($like) {
            $like->delete();

            return false;
        }

        $post->likes()->create(['user_id' => $user->id]);

        return true;
    }
}
