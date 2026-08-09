<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class FollowService
{
    /**
     * Alterna o "seguir": se já segue, deixa de seguir; se não, passa
     * a seguir. Retorna o novo estado (true = seguindo).
     *
     * @throws ValidationException
     */
    public function toggle(User $follower, User $target): bool
    {
        if ($follower->id === $target->id) {
            throw ValidationException::withMessages([
                'user' => ['Você não pode seguir a si mesmo.'],
            ]);
        }

        $existing = $follower->following()
            ->where('following_id', $target->id)
            ->first();

        if ($existing) {
            $existing->delete();

            return false;
        }

        $follower->following()->create(['following_id' => $target->id]);

        return true;
    }
}
