<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('media_url');
            $table->enum('type', ['image', 'video']);
            // Definido na criação como created_at + 24h. Stories ativas são
            // filtradas com "expires_at > now()" nas consultas — não há
            // job de limpeza automática (registros expirados apenas somem
            // das listagens, mas continuam no banco).
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['user_id', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stories');
    }
};
