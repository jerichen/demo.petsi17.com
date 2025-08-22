<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ElectionController;

Route::get('/nodes', [ElectionController::class, 'index'])->name('nodes.index');
Route::post('/election/start', [ElectionController::class, 'startElection'])->name('election.start');
Route::post('/election/reset', [ElectionController::class, 'resetElection'])->name('election.reset');
Route::post('/election/kill', [ElectionController::class, 'killLeader'])->name('election.kill');

