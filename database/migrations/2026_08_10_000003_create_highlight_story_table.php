<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('highlight_story', function (Blueprint $table) {
            $table->id();
            $table->foreignId('highlight_id')
                ->constrained('highlights')
                ->cascadeOnDelete();

            // Referência à story original, apenas para rastreabilidade.
            // Fica nula se a story original for excluída — o item do
            // destaque continua existindo normalmente, pois media_url
            // abaixo é uma CÓPIA independente do arquivo, não a mesma.
            $table->foreignId('story_id')
                ->nullable()
                ->constrained('stories')
                ->nullOnDelete();

            $table->string('media_url');
            $table->enum('type', ['image', 'video']);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('highlight_story');
    }
};
