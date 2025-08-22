<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('raft_nodes', function (Blueprint $table) {
            $table->id();
            $table->integer('zxid')->default(0);            // 模擬 Raft 的 log index
            $table->integer('term')->default(0);           // 當前任期 (Raft 最重要的概念)
            $table->unsignedBigInteger('vote_for')->nullable(); // 投給誰
            $table->enum('state', ['follower', 'candidate', 'leader'])->default('follower'); // Raft 狀態
            $table->boolean('alive')->default(true);       // 節點是否存活
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raft_nodes');
    }

};
