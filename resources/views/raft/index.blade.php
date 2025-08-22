<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>Raft Leader Election</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-900">

<div class="max-w-6xl mx-auto py-10">
    <h1 class="text-3xl font-bold mb-6">Raft Leader Election</h1>

    @if(session('status'))
        <div class="bg-green-100 text-green-800 p-3 mb-4 rounded">
            {{ session('status') }}
        </div>
    @endif

    {{-- 節點清單 --}}
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-4 py-2 border-b font-semibold">節點狀態</div>
        <div class="p-4 overflow-x-auto">
            <table class="table-auto w-full border-collapse">
                <thead>
                <tr class="bg-gray-200">
                    <th class="px-4 py-2 border">ID</th>
                    <th class="px-4 py-2 border">ZXID (lastLogIndex)</th>
                    <th class="px-4 py-2 border">Term</th>
                    <th class="px-4 py-2 border">Vote For</th>
                    <th class="px-4 py-2 border">State</th>
                    <th class="px-4 py-2 border">Alive</th>
                </tr>
                </thead>
                <tbody>
                @foreach($nodes as $node)
                    <tr class="text-center">
                        <td class="border px-4 py-2">{{ $node->id }}</td>
                        <td class="border px-4 py-2">{{ $node->zxid }}</td>
                        <td class="border px-4 py-2">{{ $node->term }}</td>
                        <td class="border px-4 py-2">{{ $node->vote_for ?? '-' }}</td>
                        <td class="border px-4 py-2">
                            @if($node->state === 'leader')
                                <span class="px-2 py-1 bg-green-100 rounded">Leader</span>
                            @elseif($node->state === 'candidate')
                                <span class="px-2 py-1 bg-yellow-100 rounded">Candidate</span>
                            @else
                                <span class="px-2 py-1 bg-blue-100 rounded">Follower</span>
                            @endif
                        </td>
                        <td class="border px-4 py-2">
                            @if($node->alive)
                                ✅
                            @else
                                ❌
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- 操作按鈕 --}}
    <div class="flex space-x-4 mb-6">
        <form method="POST" action="{{ route('raft.election.requestVote') }}">
            @csrf
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded">
                發起選舉
            </button>
        </form>
        <form method="POST" action="{{ route('raft.election.heartbeat') }}">
            @csrf
            <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded">
                發送心跳
            </button>
        </form>
        <form action="{{ route('raft.election.kill') }}" method="POST">
            @csrf
            <button class="px-4 py-2 bg-yellow-500 text-white rounded">Kill Leader</button>
        </form>
        <form method="POST" action="{{ route('raft.election.reset') }}">
            @csrf
            <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded">
                Reset
            </button>
        </form>
    </div>

    {{-- 選舉日誌 --}}
    <div class="bg-white rounded-lg shadow">
        <div class="px-4 py-2 border-b font-semibold">選舉日誌</div>
        <div class="p-4">
            @if(!empty($logs))
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($logs as $log)
                        <li>{{ $log }}</li>
                    @endforeach
                </ul>
            @else
                <p class="text-gray-500">尚無選舉紀錄</p>
            @endif
        </div>
    </div>
</div>

</body>
</html>
