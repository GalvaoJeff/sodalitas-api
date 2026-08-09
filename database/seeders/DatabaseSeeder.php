<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Follow;
use App\Models\Like;
use App\Models\Media;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Usuário fixo para você logar facilmente durante o desenvolvimento
        // do frontend, sem precisar decorar um e-mail aleatório.
        $me = User::factory()->create([
            'name' => 'Usuário Teste',
            'username' => 'teste',
            'email' => 'teste@teste.com',
        ]);

        // Mais 4 usuários aleatórios (total: 5, como combinado).
        $users = User::factory(4)->create();
        $allUsers = $users->push($me);

        // Cada usuário publica de 2 a 4 posts (soma fica perto de ~15).
        $allUsers->each(function (User $user) {
            Post::factory(rand(2, 4))
                ->for($user)
                ->create()
                ->each(function (Post $post) {
                    // Cada post tem 1 a 3 imagens, na ordem certa (position).
                    Media::factory(rand(1, 3))
                        ->for($post)
                        ->sequence(fn ($sequence) => ['position' => $sequence->index])
                        ->create();
                });
        });

        $posts = Post::all();

        // Comentários: cada post recebe de 0 a 4 comentários de usuários
        // aleatórios (incluindo o próprio autor comentando, o que é normal
        // em redes sociais).
        $posts->each(function (Post $post) use ($allUsers) {
            $commentCount = rand(0, 4);

            for ($i = 0; $i < $commentCount; $i++) {
                Comment::factory()->create([
                    'post_id' => $post->id,
                    'user_id' => $allUsers->random()->id,
                ]);
            }
        });

        // Curtidas: para cada post, uma amostra aleatória de usuários curte.
        // Usamos os IDs para evitar duplicidade (constraint única no banco).
        $posts->each(function (Post $post) use ($allUsers) {
            $likers = $allUsers->random(rand(0, $allUsers->count()));

            foreach ($likers as $user) {
                Like::create([
                    'post_id' => $post->id,
                    'user_id' => $user->id,
                ]);
            }
        });

        // Follows: cada usuário segue de 1 a 3 outros usuários (nunca a si
        // mesmo, e nunca duplicado, graças ao unique() na tabela).
        $allUsers->each(function (User $user) use ($allUsers) {
            $others = $allUsers->reject(fn (User $u) => $u->id === $user->id);
            $toFollow = $others->random(min(rand(1, 3), $others->count()));

            foreach ($toFollow as $target) {
                Follow::firstOrCreate([
                    'follower_id' => $user->id,
                    'following_id' => $target->id,
                ]);
            }
        });

        $this->command->info('Seed concluído: '.$allUsers->count().' usuários, '.$posts->count().' posts.');
        $this->command->info('Login de teste -> email: teste@teste.com | senha: password');
    }
}
