<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PostService
{
    /**
     * Monta o feed: posts do próprio usuário + de quem ele segue,
     * mais recentes primeiro, já com contadores e "curti isso" calculado.
     */
    public function feedFor(User $user, int $perPage = 10): LengthAwarePaginator
    {
        $followingIds = $user->following()->pluck('following_id');
        $authorIds = $followingIds->push($user->id);

        $posts = Post::query()
            ->whereIn('user_id', $authorIds)
            ->with(['user', 'media'])
            ->withCount(['comments', 'likes'])
            ->latest()
            ->paginate($perPage);

        $this->attachLikedByMe($posts->items(), $user);

        return $posts;
    }

    /**
     * Posts de um usuário específico (para a tela de perfil).
     */
    public function forUser(User $profileUser, ?User $viewer, int $perPage = 12): LengthAwarePaginator
    {
        $posts = Post::query()
            ->where('user_id', $profileUser->id)
            ->with(['user', 'media'])
            ->withCount(['comments', 'likes'])
            ->latest()
            ->paginate($perPage);

        if ($viewer) {
            $this->attachLikedByMe($posts->items(), $viewer);
        }

        return $posts;
    }

    /**
     * Cria um post, salva as mídias no disco público na ordem enviada.
     *
     * @param  array<string, mixed>  $data
     * @param  \Illuminate\Http\UploadedFile[]  $mediaFiles
     */
    public function create(User $user, array $data, array $mediaFiles = []): Post
    {
        $post = Post::create([
            'user_id' => $user->id,
            'caption' => $data['caption'] ?? null,
        ]);

        foreach ($mediaFiles as $position => $file) {
            $path = $file->store('posts', 'public');

            $isVideo = str_starts_with($file->getMimeType() ?? '', 'video');

            $post->media()->create([
                'type' => $isVideo ? 'video' : 'image',
                'url' => asset('storage/'.$path),
                'position' => $position,
            ]);
        }

        return $post->load(['user', 'media']);
    }

    /**
     * Remove um post (e suas mídias do disco), garantindo que só o
     * dono do post possa apagá-lo.
     *
     * @throws ValidationException
     */
    public function delete(Post $post, User $user): void
    {
        if ($post->user_id !== $user->id) {
            throw ValidationException::withMessages([
                'post' => ['Você não tem permissão para excluir este post.'],
            ]);
        }

        foreach ($post->media as $media) {
            if (str_contains($media->url, '/storage/')) {
                $relativePath = Str::after($media->url, '/storage/');
                Storage::disk('public')->delete($relativePath);
            }
        }

        $post->delete();
    }

    /**
     * Carrega um post completo (autor, mídia, comentários) já com
     * liked_by_me calculado para o usuário autenticado, se houver.
     */
    public function findForViewer(Post $post, ?User $user): Post
    {
        $post->load(['user', 'media', 'comments.user'])
            ->loadCount(['comments', 'likes']);

        if ($user) {
            $liked = $post->likes()->where('user_id', $user->id)->exists();
            $post->setAttribute('liked_by_me', $liked);
        }

        return $post;
    }

    /**
     * Marca, em memória, se o usuário autenticado curtiu cada post da
     * coleção, evitando N+1 queries (uma consulta só para todos os posts).
     *
     * @param  Post[]  $posts
     */
    private function attachLikedByMe(array $posts, User $user): void
    {
        $postIds = array_map(fn (Post $post) => $post->id, $posts);

        $likedPostIds = $user->likes()
            ->whereIn('post_id', $postIds)
            ->pluck('post_id')
            ->flip();

        foreach ($posts as $post) {
            $post->setAttribute('liked_by_me', $likedPostIds->has($post->id));
        }
    }
}