<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HighlightStory extends Model
{
    protected $table = 'highlight_story';

    protected $fillable = [
        'highlight_id',
        'story_id',
        'media_url',
        'type',
        'position',
    ];

    public function highlight(): BelongsTo
    {
        return $this->belongsTo(Highlight::class);
    }

    /**
     * Story original (pode ser null se ela já tiver sido excluída — o
     * item do destaque continua funcionando normalmente mesmo assim).
     */
    public function story(): BelongsTo
    {
        return $this->belongsTo(Story::class);
    }
}
