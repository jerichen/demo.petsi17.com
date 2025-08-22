<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>ZooKeeper Leader Election</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-900">

<h1 class="text-2xl font-bold mb-4">ZooKeeper Leader Election 模擬</h1>

@if(session('status'))
    <div class="bg-green-100 text-green-800 p-3 mb-4 rounded">
        {{ session('status') }}
    </div>
@endif

<table class="table-auto border w-full mb-4">
    <thead class="bg-gray-200">
    <tr>
        <th class="border px-4 py-2">Node ID</th>
        <th class="border px-4 py-2">Alive</th>
        <th class="border px-4 py-2">ZXID</th>
        <th class="border px-4 py-2">投給</th>
        <th class="border px-4 py-2">狀態</th>
    </tr>
    </thead>
    <tbody>
    @foreach($nodes as $node)
        <tr class="{{!$node->alive ? 'bg-red-100':''}} {{ $node->state==='leader' ? 'bg-blue-100':'' }}">
            <td class="border px-4 py-2">{{ $node->id }}</td>
            <td class="border px-4 py-2">{{$node->alive ? 'Y':'N'}}</td>
            <td class="border px-4 py-2">{{ $node->zxid }}</td>
            <td class="border px-4 py-2">{{ $node->vote_for ?? '-' }}</td>
            <td class="border px-4 py-2">
                @if($node->state === 'leader')
                    🏆 <span class="font-bold text-green-600">Leader</span>
                @elseif($node->state === 'follower')
                    👥 Follower
                @else
                    ❓ Looking
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="flex space-x-2 mb-6">
    <form action="{{ route('election.start') }}" method="POST">
        @csrf
        <button class="px-4 py-2 bg-blue-500 text-white rounded">開始選舉</button>
    </form>

    <form action="{{ route('election.reset') }}" method="POST">
        @csrf
        <button class="px-4 py-2 bg-red-500 text-white rounded">Reset</button>
    </form>

    <form action="{{ route('election.kill') }}" method="POST">
        @csrf
        <button class="px-4 py-2 bg-yellow-500 text-white rounded">Kill Leader</button>
    </form>
</div>

<h2 class="text-xl font-bold mb-2">選舉過程紀錄</h2>
<div class="bg-white border p-4 rounded shadow max-h-64 overflow-y-auto">
    @forelse($logs as $log)
        <p>• {{ $log }}</p>
    @empty
        <p class="text-gray-500">目前沒有紀錄</p>
    @endforelse
</div>

</body>
</html>
