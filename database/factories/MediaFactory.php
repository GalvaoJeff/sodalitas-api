<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Media>
 */
class MediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'type' => 'image',
            // picsum.photos gera uma imagem aleatória real a cada seed diferente
            'url' => 'https://picsum.photos/seed/'.fake()->uuid().'/600/600',
            'position' => 0,
        ];
    }

    /**
     * Estado para gerar uma mídia do tipo vídeo.
     */
    public function video(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'video',
            'url' => 'https://www.w3schools.com/html/mov_bbb.mp4',
        ]);
    }
}
