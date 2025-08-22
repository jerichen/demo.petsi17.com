<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ElectionController;
use App\Http\Controllers\RaftElectionController;

Route::get('/nodes', [ElectionController::class, 'index'])->name('nodes.index');
Route::post('/election/start', [ElectionController::class, 'startElection'])->name('election.start');
Route::post('/election/reset', [ElectionController::class, 'resetElection'])->name('election.reset');
Route::post('/election/kill', [ElectionController::class, 'killLeader'])->name('election.kill');

Route::get('/raft/nodes', [RaftElectionController::class, 'index'])->name('raft.nodes.index');
Route::post('/raft/election/request-vote', [RaftElectionController::class, 'requestVote'])->name('raft.election.requestVote');
Route::post('/raft/election/vote', [RaftElectionController::class, 'castVote'])->name('raft.election.castVote');
Route::post('/raft/election/heartbeat', [RaftElectionController::class, 'sendHeartbeat'])->name('raft.election.heartbeat');
Route::post('/raft/election/reset', [RaftElectionController::class, 'resetElection'])->name('raft.election.reset');
Route::post('/raft/election/kill', [RaftElectionController::class, 'killLeader'])->name('raft.election.kill');
