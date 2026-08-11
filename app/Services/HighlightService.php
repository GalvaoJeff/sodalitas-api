<?php

namespace App\Services;

use App\Models\Highlight;
use App\Models\HighlightStory;
use App\Models\Story;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class HighlightService
{
    /**
     * Destaques de um usuário, já com os itens carregados, para exibir
     * na tela de perfil.
     */
    public function listForUser(User $profileUser): Collection
    {
        return Highlight::query()
            ->where('user_id', $profileUser->id)
            ->with('items')
            ->latest()
            ->get();
    }

    /**
     * Cria um destaque vazio (sem itens ainda) com o título informado.
     */
    public function create(User $user, string $title): Highlight
    {
        return Highlight::create([
            'user_id' => $user->id,
            'title' => $title,
        ]);
    }

    /**
     * Adiciona uma story a um destaque. Importante: o arquivo de mídia
     * é COPIADO para um caminho próprio ('highlights/...'), tornando o
     * item do destaque totalmente independente da story original —
     * mesmo que ela expire e seja excluída depois, o destaque continua
     * exibindo a imagem normalmente.
     *
     * @throws ValidationException
     */
    public function addStory(Highlight $highlight, Story $story, User $user): HighlightStory
    {
        if ($highlight->user_id !== $user->id) {
            throw ValidationException::withMessages([
                'highlight' => ['Você não tem permissão para editar este destaque.'],
            ]);
        }

        if ($story->user_id !== $user->id) {
            throw ValidationException::withMessages([
                'story' => ['Você só pode adicionar suas próprias stories a um destaque.'],
            ]);
        }

        $copiedUrl = $this->copyStoryMediaToHighlights($story);

        $nextPosition = ($highlight->items()->max('position') ?? -1) + 1;

        $item = $highlight->items()->create([
            'story_id' => $story->id,
            'media_url' => $copiedUrl,
            'type' => $story->type,
            'position' => $nextPosition,
        ]);

        if (! $highlight->cover_url) {
            $highlight->update(['cover_url' => $copiedUrl]);
        }

        return $item;
    }

    /**
     * Remove um item específico de dentro de um destaque (e seu
     * arquivo copiado do disco).
     *
     * @throws ValidationException
     */
    public function removeStory(HighlightStory $item, User $user): void
    {
        if ($item->highlight->user_id !== $user->id) {
            throw ValidationException::withMessages([
                'highlight' => ['Você não tem permissão para editar este destaque.'],
            ]);
        }

        $this->deleteFileIfLocal($item->media_url);
        $item->delete();
    }

    /**
     * Exclui um destaque inteiro, incluindo todos os arquivos copiados
     * dos seus itens.
     *
     * @throws ValidationException
     */
    public function delete(Highlight $highlight, User $user): void
    {
        if ($highlight->user_id !== $user->id) {
            throw ValidationException::withMessages([
                'highlight' => ['Você não tem permissão para excluir este destaque.'],
            ]);
        }

        foreach ($highlight->items as $item) {
            $this->deleteFileIfLocal($item->media_url);
        }

        $highlight->delete(); // cascade apaga os registros de highlight_story
    }

    /**
     * Copia o arquivo físico da story para dentro de storage/app/public/highlights,
     * retornando a URL absoluta do novo arquivo.
     */
    private function copyStoryMediaToHighlights(Story $story): string
    {
        $originalPath = Str::after($story->media_url, '/storage/');
        $extension = pathinfo($originalPath, PATHINFO_EXTENSION);
        $newPath = 'highlights/'.Str::uuid().'.'.$extension;

        Storage::disk('public')->copy($originalPath, $newPath);

        return asset('storage/'.$newPath);
    }

    private function deleteFileIfLocal(string $url): void
    {
        if (str_contains($url, '/storage/')) {
            $relativePath = Str::after($url, '/storage/');
            Storage::disk('public')->delete($relativePath);
        }
    }
}
