<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UserService
{
    /**
     * Atualiza os dados de perfil do usuário autenticado.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateProfile(User $user, array $data, ?UploadedFile $avatar = null): User
    {
        if ($avatar) {
            // Remove o avatar antigo do disco, se existir e não for um
            // link externo (ex: avatar de seed vindo do pravatar.cc).
            if ($user->avatar_url && str_starts_with($user->avatar_url, '/storage/')) {
                Storage::disk('public')->delete(
                    str_replace('/storage/', '', $user->avatar_url)
                );
            }

            $path = $avatar->store('avatars', 'public');
            $data['avatar_url'] = '/storage/'.$path;
        }

        $user->fill($data);
        $user->save();

        return $user;
    }

    /**
     * Lista/busca usuários por nome ou username (tela de pesquisa).
     * Sem termo de busca, retorna todos os usuários paginados.
     * O próprio usuário autenticado é sempre excluído dos resultados,
     * já que "seguir a si mesmo" não é uma ação válida.
     */
    public function search(?string $query, ?User $viewer, int $perPage = 20)
    {
        return User::query()
            ->when($viewer, fn ($q) => $q->where('id', '!=', $viewer->id))
            ->when($query, function ($q) use ($query) {
                $q->where(function ($inner) use ($query) {
                    $inner->where('name', 'like', "%{$query}%")
                        ->orWhere('username', 'like', "%{$query}%");
                });
            })
            ->withCount(['posts', 'followers', 'following'])
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Sugere usuários que o usuário autenticado ainda não segue, para a
     * seção "sugestões para seguir" da Home.
     */
    public function suggestions(User $viewer, int $limit = 5)
    {
        $followingIds = $viewer->following()->pluck('following_id');

        return User::query()
            ->where('id', '!=', $viewer->id)
            ->whereNotIn('id', $followingIds)
            ->withCount(['posts', 'followers', 'following'])
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    /**
     * Retorna o perfil de um usuário, já marcando se o usuário autenticado
     * (se houver) o segue ou não, se é o próprio dono do perfil, e
     * mascarando o telefone quando o visitante não tem permissão de vê-lo.
     */
    public function findProfile(string $username, ?User $viewer): User
    {
        $user = User::query()
            ->where('username', $username)
            ->withCount(['posts', 'followers', 'following'])
            ->firstOrFail();

        $isOwner = $viewer && $viewer->id === $user->id;
        $user->setAttribute('own_profile', $isOwner);

        if ($isOwner) {
            // O dono sempre vê e pode alterar seu próprio telefone.
            return $user;
        }

        if ($viewer) {
            $isFollowing = $viewer->following()
                ->where('following_id', $user->id)
                ->exists();

            $user->setAttribute('is_followed_by_me', $isFollowing);

            $isFollowedBack = $user->following()
                ->where('following_id', $viewer->id)
                ->exists();

            $isMutualFollow = $isFollowing && $isFollowedBack;

            // Telefone só aparece para "amigos" (seguem um ao outro) E se
            // o dono do perfil optou por exibir (show_phone = true).
            if (! ($isMutualFollow && $user->show_phone)) {
                $user->setAttribute('phone', null);
            }
        } else {
            // Visitante não autenticado nunca vê telefone.
            $user->setAttribute('phone', null);
        }

        return $user;
    }
}