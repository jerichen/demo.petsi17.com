<?php

namespace App\Http\Controllers;

use App\Models\Node;
use Illuminate\Support\Facades\Session;

class ElectionController extends Controller
{
    public function index()
    {
        $nodes = Node::all();
        $logs = Session::get('logs', []);
        return view('nodes.index', compact('nodes', 'logs'));
    }

    public function startElection()
    {
        $epoch = now()->timestamp;
        $nodes = Node::where('alive', true)->get();

        $logs = [];
        $logs[] = "🚀 新一輪選舉開始 (epoch={$epoch})";

        // 每個節點先投自己
        foreach ($nodes as $node) {
            $node->update([
                'epoch' => $epoch,
                'vote_for' => $node->id,
                'state' => 'looking',
            ]);
            $logs[] = "Node {$node->id} 投給自己 (zxid={$node->zxid})";
        }

        // 比大小：先比 zxid，再比 id
        $winner = $nodes->sort(function ($a, $b) {
            if ($a->zxid === $b->zxid) {
                // 如果 zxid 相同，就比 id
                return $b->id <=> $a->id;
            }
            // 否則比 zxid
            return $b->zxid <=> $a->zxid;
        })->first();

        $logs[] = "📊 節點們比較：最高 zxid/id = Node {$winner->id}";

        // 模擬「過半數投票集中」
        foreach ($nodes as $node) {
            if ($node->id != $winner->id) {
                $logs[] = "Node {$node->id} 改投給 Node {$winner->id}";
            }
            $node->update([
                'vote_for' => $winner->id,
                'state' => $node->id == $winner->id ? 'leader' : 'follower',
            ]);
        }

        $logs[] = "🏆 Node {$winner->id} 當選為 Leader";

        Session::put('logs', $logs);

        return redirect()->route('nodes.index');
    }

    public function resetElection()
    {
        // 清空舊的節點
        Node::truncate();

        $nodes = collect();

        // 建立 6 個節點
        for ($i = 1; $i <= 6; $i++) {
            $nodes->push(Node::create([
                'zxid' => rand(1, 100),
                'epoch' => 0,
                'vote_for' => null,
                'state' => 'Follower',
            ]));
        }

        // 指定初始 Leader（zxid 最大者）
        $leader = $nodes->sort(function ($a, $b) {
            if ($a->zxid === $b->zxid) {
                return $b->id <=> $a->id;
            }
            return $b->zxid <=> $a->zxid;
        })->first();

        $leader->update(['state' => 'leader']);

        Session::put('logs', ["✅ 系統初始化完成，Node {$leader->id} 被指定為初始 Leader (zxid={$leader->zxid})"]);

        return redirect()->route('nodes.index')
                         ->with('status', "已重置，產生 6 個節點，指定初始 Leader");
    }

    public function killLeader()
    {
        $leader = Node::where('state', 'leader')->first();

        if (!$leader) {
            return redirect()->route('nodes.index')->with('status', "系統目前沒有 Leader");
        }

        // 標記 leader 掛掉
        $leader->update([
            'state' => 'looking',
            'alive' => false,
            'vote_for' => null,
        ]);

        $logs = Session::get('logs', []);
        $logs[] = "Node {$leader->id} (zxid={$leader->zxid}) Leader 掛掉，系統暫時沒有 Leader";
        Session::put('logs', $logs);

        return redirect()->route('nodes.index')->with('status', "Leader 已掛掉，等待重新選舉");
    }

}
