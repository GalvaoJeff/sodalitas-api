<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Highlight extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'cover_url',
    ];

    /**
     * Dono do destaque.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Itens (stories copiadas) dentro deste destaque, na ordem em que
     * foram adicionadas.
     */
    public function items(): HasMany
    {
        return $this->hasMany(HighlightStory::class)->orderBy('position');
    }
}
