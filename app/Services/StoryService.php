<?php

namespace App\Services;

use App\Models\Story;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StoryService
{
    /**
     * Monta os grupos de stories para o carrossel do feed: do próprio
     * usuário + de quem ele segue, apenas as ainda não expiradas.
     * O grupo do próprio usuário sempre vem primeiro; os demais são
     * ordenados pela story mais recente de cada autor.
     *
     * @return array<int, array{user: User, stories: Collection<int, Story>}>
     */
    public function activeForFeed(User $user): array
    {
        $followingIds = $user->following()->pluck('following_id');
        $authorIds = $followingIds->push($user->id);

        $stories = Story::query()
            ->whereIn('user_id', $authorIds)
            ->active()
            ->with('user')
            ->orderBy('created_at')
            ->get();

        $grouped = $stories
            ->groupBy('user_id')
            ->sortByDesc(fn (Collection $group) => $group->max('created_at'));

        // Garante que o próprio usuário apareça primeiro no carrossel,
        // mesmo que a story mais recente dele não seja a mais nova.
        $ownGroup = $grouped->pull($user->id);
        if ($ownGroup) {
            $grouped = collect([$user->id => $ownGroup])->merge($grouped);
        }

        return $grouped
            ->map(fn (Collection $userStories) => [
                'user' => $userStories->first()->user,
                'stories' => $userStories->values(),
            ])
            ->values()
            ->all();
    }

    /**
     * Stories ativas de um único usuário (usado ao tocar no avatar de
     * alguém específico, fora do carrossel do feed).
     */
    public function forUser(User $profileUser): Collection
    {
        return Story::query()
            ->where('user_id', $profileUser->id)
            ->active()
            ->with('user')
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Cria uma story a partir de um único arquivo de mídia, expirando
     * automaticamente 24h após a criação.
     */
    public function create(User $user, UploadedFile $file): Story
    {
        $path = $file->store('stories', 'public');

        $isVideo = str_starts_with($file->getMimeType() ?? '', 'video');

        return Story::create([
            'user_id' => $user->id,
            'media_url' => asset('storage/'.$path),
            'type' => $isVideo ? 'video' : 'image',
            'expires_at' => now()->addDay(),
        ])->load('user');
    }

    /**
     * Remove uma story (e seu arquivo do disco), garantindo que só o
     * dono possa excluí-la. Itens de destaque que copiaram esta story
     * não são afetados, pois possuem seu próprio arquivo independente.
     *
     * @throws ValidationException
     */
    public function delete(Story $story, User $user): void
    {
        if ($story->user_id !== $user->id) {
            throw ValidationException::withMessages([
                'story' => ['Você não tem permissão para excluir esta story.'],
            ]);
        }

        if (str_contains($story->media_url, '/storage/')) {
            $relativePath = Str::after($story->media_url, '/storage/');
            Storage::disk('public')->delete($relativePath);
        }

        $story->delete();
    }
}
