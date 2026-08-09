<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('birthdate')->nullable()->after('avatar_url');
            $table->string('location', 120)->nullable()->after('birthdate');
            $table->string('profession', 120)->nullable()->after('location');
            $table->string('education', 120)->nullable()->after('profession');
            $table->string('phone', 30)->nullable()->after('education');
            // Lista simples separada por vírgula (ex: "fotografia, games, trilhas").
            // Guardamos como texto por simplicidade; dá para migrar para JSON
            // depois se precisar de hobbies estruturados/filtráveis.
            $table->text('hobbies')->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'birthdate',
                'location',
                'profession',
                'education',
                'phone',
                'hobbies',
            ]);
        });
    }
};
