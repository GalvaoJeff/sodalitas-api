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
     * Busca usuários por nome ou username (para a tela de pesquisa).
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    public function search(string $query, int $limit = 20)
    {
        return User::query()
            ->where('name', 'like', "%{$query}%")
            ->orWhere('username', 'like', "%{$query}%")
            ->withCount(['posts', 'followers', 'following'])
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
