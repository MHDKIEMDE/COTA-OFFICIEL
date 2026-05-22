@extends('admin.layouts.app')
@section('title', 'Monitoring APIs')

@section('content')
<div class="p-6 space-y-6">

  {{-- En-tête --}}
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-brand font-black text-ink">Monitoring APIs</h1>
      <p class="text-sm text-dim mt-1">Consommation des quotas en temps réel — {{ now()->format('d/m/Y H:i') }}</p>
    </div>
    <div class="flex items-center gap-2">
      <span class="w-2 h-2 rounded-full {{ $redisOk ? 'bg-win' : 'bg-loss' }}"></span>
      <span class="text-xs text-dim font-mono">Redis {{ $redisOk ? 'OK' : 'KO' }}</span>
    </div>
  </div>

  {{-- Jauges quotas --}}
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

    {{-- API-Football journalier --}}
    @php $pctDay = $footballDailyLimit > 0 ? min(100, round($footballDailyUsed / $footballDailyLimit * 100)) : 0; @endphp
    <div class="bg-bg-2 border border-line rounded-xl p-5">
      <div class="flex items-center justify-between mb-3">
        <span class="text-xs font-mono text-dim uppercase tracking-widest">API-Football / jour</span>
        <span class="text-xs font-mono {{ $pctDay >= 90 ? 'text-loss' : ($pctDay >= 70 ? 'text-yellow-400' : 'text-win') }}">
          {{ $footballDailyUsed }} / {{ $footballDailyLimit }}
        </span>
      </div>
      <div class="w-full bg-bg-3 rounded-full h-2">
        <div class="h-2 rounded-full transition-all {{ $pctDay >= 90 ? 'bg-loss' : ($pctDay >= 70 ? 'bg-yellow-400' : 'bg-win') }}"
             style="width: {{ $pctDay }}%"></div>
      </div>
      <p class="text-xs text-dim mt-2">{{ 100 - $pctDay }}% restant aujourd'hui</p>
    </div>

    {{-- API-Football /min --}}
    @php $pctMin = $footballMinLimit > 0 ? min(100, round($footballMinUsed / $footballMinLimit * 100)) : 0; @endphp
    <div class="bg-bg-2 border border-line rounded-xl p-5">
      <div class="flex items-center justify-between mb-3">
        <span class="text-xs font-mono text-dim uppercase tracking-widest">API-Football / min</span>
        <span class="text-xs font-mono {{ $pctMin >= 90 ? 'text-loss' : 'text-win' }}">
          {{ $footballMinUsed }} / {{ $footballMinLimit }}
        </span>
      </div>
      <div class="w-full bg-bg-3 rounded-full h-2">
        <div class="h-2 rounded-full {{ $pctMin >= 90 ? 'bg-loss' : 'bg-win' }}" style="width: {{ $pctMin }}%"></div>
      </div>
      <p class="text-xs text-dim mt-2">Fenêtre glissante 1 min</p>
    </div>

    {{-- Prédictions générées aujourd'hui --}}
    <div class="bg-bg-2 border border-line rounded-xl p-5">
      <div class="flex items-center justify-between mb-3">
        <span class="text-xs font-mono text-dim uppercase tracking-widest">Prédictions générées</span>
        <span class="text-xs font-mono text-accent">aujourd'hui</span>
      </div>
      <p class="text-3xl font-brand font-black text-ink">{{ $predictionsToday }}</p>
      <p class="text-xs text-dim mt-2">Via GenerateAllPredictionsJob</p>
    </div>
  </div>

  {{-- RapidAPI calls --}}
  @if(!empty($rapidApiCalls))
  <div class="bg-bg-2 border border-line rounded-xl p-5">
    <h2 class="text-sm font-mono text-dim uppercase tracking-widest mb-4">RapidAPI — Appels du jour</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
      @foreach($rapidApiCalls as $service => $count)
      <div class="bg-bg-3 rounded-lg p-3 text-center">
        <p class="text-xl font-brand font-black text-ink">{{ $count }}</p>
        <p class="text-xs text-dim mt-1 truncate">{{ $service }}</p>
      </div>
      @endforeach
    </div>
  </div>
  @endif

  {{-- Appels par endpoint --}}
  @if(!empty($endpointCalls))
  <div class="bg-bg-2 border border-line rounded-xl p-5">
    <h2 class="text-sm font-mono text-dim uppercase tracking-widest mb-4">Appels par endpoint (aujourd'hui)</h2>
    <div class="space-y-2">
      @foreach(collect($endpointCalls)->sortByDesc(fn($v) => $v)->take(15) as $endpoint => $count)
      <div class="flex items-center gap-3">
        <span class="font-mono text-xs text-accent w-10 text-right shrink-0">{{ $count }}</span>
        <div class="flex-1 bg-bg-3 rounded-full h-1.5">
          <div class="h-1.5 rounded-full bg-accent/40"
               style="width: {{ min(100, $count / max(array_values($endpointCalls)) * 100) }}%"></div>
        </div>
        <span class="font-mono text-xs text-dim truncate">{{ $endpoint }}</span>
      </div>
      @endforeach
    </div>
  </div>
  @endif

  {{-- Graphique 7 jours --}}
  <div class="bg-bg-2 border border-line rounded-xl p-5">
    <h2 class="text-sm font-mono text-dim uppercase tracking-widest mb-4">Consommation 7 derniers jours</h2>
    <canvas id="quotaChart" height="80"></canvas>
  </div>

  {{-- Jobs schedulés --}}
  <div class="bg-bg-2 border border-line rounded-xl p-5">
    <h2 class="text-sm font-mono text-dim uppercase tracking-widest mb-4">Dernière exécution des Jobs</h2>
    <div class="divide-y divide-line">
      @foreach($lastJobs as $job => $lastRun)
      <div class="flex items-center justify-between py-2.5">
        <span class="text-sm font-mono text-ink-2">{{ $job }}</span>
        @if($lastRun)
          @php $diff = \Carbon\Carbon::parse($lastRun)->diffForHumans(); @endphp
          <span class="text-xs font-mono text-win">{{ $diff }}</span>
        @else
          <span class="text-xs font-mono text-dim">Jamais exécuté</span>
        @endif
      </div>
      @endforeach
    </div>
  </div>

</div>

<script>
const labels = @json(array_column($history7d, 'date'));
const footballData = @json(array_column($history7d, 'football'));
const rapidData    = @json(array_column($history7d, 'rapidapi'));

new Chart(document.getElementById('quotaChart'), {
  type: 'bar',
  data: {
    labels,
    datasets: [
      {
        label: 'API-Football',
        data: footballData,
        backgroundColor: 'rgba(232,255,54,0.7)',
        borderRadius: 4,
      },
      {
        label: 'RapidAPI',
        data: rapidData,
        backgroundColor: 'rgba(61,220,145,0.5)',
        borderRadius: 4,
      },
    ],
  },
  options: {
    responsive: true,
    plugins: { legend: { labels: { color: '#8b8a85', font: { family: 'JetBrains Mono', size: 11 } } } },
    scales: {
      x: { ticks: { color: '#8b8a85' }, grid: { color: '#1d2026' } },
      y: { ticks: { color: '#8b8a85' }, grid: { color: '#1d2026' }, beginAtZero: true },
    },
  },
});
</script>
@endsection
