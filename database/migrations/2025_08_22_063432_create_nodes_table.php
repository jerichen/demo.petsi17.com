<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nodes', function (Blueprint $table) {
            $table->id();
            $table->integer('zxid'); // 模擬資料新舊
            $table->integer('epoch')->default(0); // 當前選舉輪次
            $table->unsignedBigInteger('vote_for')->nullable(); // 投給誰
            $table->enum('state', ['looking', 'leader', 'follower'])->default('looking');
            $table->boolean('alive')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nodes');
    }
};
