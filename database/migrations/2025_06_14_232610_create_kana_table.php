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
        Schema::create('kanas', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('kana');
            $table->string('romaji')->nullable();
            $table->string('onyomi')->nullable();
            $table->string('kunyomi')->nullable();
            $table->string('meaning')->nullable();
            $table->boolean('learned')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kanas');
    }
};
