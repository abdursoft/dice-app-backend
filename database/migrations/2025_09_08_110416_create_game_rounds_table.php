<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('game_rounds', function (Blueprint $table) {
            $table->id();
            $table->string('round_id')->unique(bin2hex(random_bytes(6)));
            $table->text('message')->nullable();
            $table->bigInteger('game_turn')->default(0);
            $table->enum('status',['playing','pause','completed'])->default('playing');
            $table->foreignId('game_challenge_id')->constrained('game_challenges')->cascadeOnDelete();
            $table->foreignId('first_player')->constrained('users')->cascadeOnDelete();
            $table->foreignId('second_player')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_rounds');
    }
};
