<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CommentService
{
    /**
     * Cria um comentário em um post.
     */
    public function create(Post $post, User $user, string $content): Comment
    {
        $comment = $post->comments()->create([
            'user_id' => $user->id,
            'content' => $content,
        ]);

        return $comment->load('user');
    }

    /**
     * Remove um comentário. Permitido tanto para quem escreveu o
     * comentário quanto para o dono do post (como no Instagram real).
     *
     * @throws ValidationException
     */
    public function delete(Comment $comment, User $user): void
    {
        $isCommentAuthor = $comment->user_id === $user->id;
        $isPostOwner = $comment->post->user_id === $user->id;

        if (! $isCommentAuthor && ! $isPostOwner) {
            throw ValidationException::withMessages([
                'comment' => ['Você não tem permissão para excluir este comentário.'],
            ]);
        }

        $comment->delete();
    }
}
