<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    /** @use HasFactory<\Database\Factories\PostFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'caption',
    ];

    /**
     * Autor do post.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Imagens/vídeos anexados ao post, na ordem de exibição.
     */
    public function media(): HasMany
    {
        return $this->hasMany(Media::class)->orderBy('position');
    }

    /**
     * Comentários do post.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Curtidas do post.
     */
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }
}
