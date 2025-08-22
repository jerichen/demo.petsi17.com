<?php

namespace App\Http\Controllers;

use App\Models\RaftNode;
use Illuminate\Support\Facades\Session;

class RaftElectionController extends Controller
{
    public function index()
    {
        $nodes = RaftNode::all();
        $logs = Session::get('logs', []);
        return view('raft.index', compact('nodes', 'logs'));
    }

    public function requestVote()
    {
        $term = now()->timestamp; // 用時間戳模擬 term
        $nodes = RaftNode::where('alive', true)->get();

        $logs = [];
        $logs[] = "🚀 Raft 新一輪選舉開始 (term={$term})";

        // 節點轉成 Candidate，先投自己
        foreach ($nodes as $node) {
            $node->update([
                'term' => $term,
                'vote_for' => $node->id,
                'state' => 'candidate',
            ]);
            $logs[] = "Node {$node->id} 成為 Candidate，並投給自己 (lastLogIndex={$node->zxid})";
        }

        // 模擬 RequestVote：比 lastLogIndex，如果一樣則隨機偏好
        $winner = $nodes->sort(function ($a, $b) {
            if ($a->zxid === $b->zxid) {
                return $b->id <=> $a->id; // 如果 log 相同，就比 id
            }
            return $b->zxid <=> $a->zxid;
        })->first();

        $logs[] = "📊 節點們比對 log，新任 Leader 候選人：Node {$winner->id}";

        // 模擬過半投票
        $majority = intval($nodes->count() / 2) + 1;
        $votes = 0;
        foreach ($nodes as $node) {
            if ($node->id == $winner->id) {
                $votes++;
            } else {
                $votes++;
                $logs[] = "Node {$node->id} 投票給 Node {$winner->id}";
            }
        }

        if ($votes >= $majority) {
            foreach ($nodes as $node) {
                $node->update([
                    'vote_for' => $winner->id,
                    'state' => $node->id == $winner->id ? 'leader' : 'follower',
                ]);
            }
            $logs[] = "🏆 Node {$winner->id} 當選為 Leader (term={$term}, 獲得 {$votes} 票)";
        } else {
            $logs[] = "⚠️ 沒有候選人拿到過半票數，選舉失敗，等待下一輪 timeout";
        }

        Session::put('logs', $logs);
        return redirect()->route('raft.nodes.index');
    }

    public function resetElection()
    {
        RaftNode::truncate();
        $nodes = collect();

        // 建立 6 個節點
        for ($i = 1; $i <= 6; $i++) {
            $nodes->push(RaftNode::create([
                'zxid' => rand(1, 100),
                'term' => 0,
                'vote_for' => null,
                'state' => 'follower',
                'alive' => true,
            ]));
        }

        Session::put('logs', ["✅ Raft 系統初始化完成，所有節點為 follower，等待心跳或選舉"]);
        return redirect()->route('raft.nodes.index')
                         ->with('status', "已重置，產生 6 個節點 (Raft)");
    }

    public function sendHeartbeat()
    {
        $leader = RaftNode::where('state', 'leader')->first();

        if (!$leader) {
            return redirect()->route('raft.nodes.index')->with('status', "⚠️ 沒有 Leader，無法發送心跳");
        }

        $logs = Session::get('logs', []);
        $logs[] = "Leader Node {$leader->id} 發送心跳，Follower 保持同步 (term={$leader->term})";
        Session::put('logs', $logs);

        return redirect()->route('raft.nodes.index')->with('status', "Leader 發送心跳成功");
    }

    public function killLeader()
    {
        $leader = RaftNode::where('state', 'leader')->first();

        if (!$leader) {
            return redirect()->route('raft.nodes.index')
                             ->with('status', "⚠️ 系統目前沒有 Leader");
        }

        // 標記 Leader 掛掉
        $leader->update([
            'state' => 'follower',  // Leader 掛掉後先變回 follower（或可用 dead 標記）
            'alive' => false,
            'vote_for' => null,
        ]);

        $logs = Session::get('logs', []);
        $logs[] = "Node {$leader->id} Leader 掛掉，開始重新選舉";

        // 所有活著的 Follower 變成 Candidate
        $nodes = RaftNode::where('alive', true)->get();
        foreach ($nodes as $node) {
            $node->update([
                'state' => 'candidate',
                'term' => $node->term + 1,
                'vote_for' => $node->id, // 先投自己一票
            ]);
            $logs[] = "Node {$node->id} 變成 Candidate，並投給自己 (term={$node->term})";
        }

        // 模擬選舉：找 zxid 最大者，如果一樣就比 ID
        $winner = $nodes->sort(function ($a, $b) {
            if ($a->zxid === $b->zxid) {
                return $b->id <=> $a->id;
            }
            return $b->zxid <=> $a->zxid;
        })->first();

        $majority = intval($nodes->count() / 2) + 1;
        $votes = 0;

        foreach ($nodes as $node) {
            if ($node->id != $winner->id) {
                $logs[] = "Node {$node->id} 投票給 Node {$winner->id}";
            }
            $votes++;
            $node->update([
                'vote_for' => $winner->id,
                'state' => $node->id == $winner->id ? 'leader' : 'follower',
            ]);
        }

        if ($votes >= $majority) {
            $logs[] = "🏆 Node {$winner->id} 當選為新 Leader (term={$winner->term}, 獲得 {$votes} 票)";
        } else {
            $logs[] = "⚠️ 沒有候選人取得過半票數，選舉失敗";
        }

        Session::put('logs', $logs);

        return redirect()->route('raft.nodes.index')
                         ->with('status', "Leader 已掛掉，系統重新選舉完成");
    }
}
