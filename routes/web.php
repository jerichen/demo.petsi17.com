<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ElectionController;
use App\Http\Controllers\RaftElectionController;

Route::get('/nodes', [ElectionController::class, 'index'])->name('nodes.index');
Route::post('/election/start', [ElectionController::class, 'startElection'])->name('election.start');
Route::post('/election/reset', [ElectionController::class, 'resetElection'])->name('election.reset');
Route::post('/election/kill', [ElectionController::class, 'killLeader'])->name('election.kill');


// Raft Election Routes
Route::get('/raft/nodes', [RaftElectionController::class, 'index'])->name('raft.nodes.index');
// 發起選舉（candidate 發 RequestVote）
Route::post('/raft/election/request-vote', [RaftElectionController::class, 'requestVote'])->name('raft.election.requestVote');
// 投票給候選人
Route::post('/raft/election/vote', [RaftElectionController::class, 'castVote'])->name('raft.election.castVote');
// Leader 發送心跳（AppendEntries）
Route::post('/raft/election/heartbeat', [RaftElectionController::class, 'sendHeartbeat'])->name('raft.election.heartbeat');
// 重置整個集群狀態
Route::post('/raft/election/reset', [RaftElectionController::class, 'resetElection'])->name('raft.election.reset');
