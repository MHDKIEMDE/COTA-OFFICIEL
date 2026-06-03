@extends('admin.layouts.app')
@section('title', 'Funnel Acquisition')

@section('content')
<div class="p-6 max-w-4xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-black" style="color:var(--ink); font-family:Archivo,sans-serif">
                Funnel Acquisition
            </h1>
            <p class="text-xs mt-1" style="color:var(--dim)">
                Sessions uniques — {{ $days }} derniers jours
            </p>
        </div>
        <a href="{{ route('admin.stats.index') }}"
           class="text-xs px-3 py-1 rounded"
           style="background:var(--bg-3); color:var(--dim); border:1px solid var(--line)">
            ← Statistiques
        </a>
    </div>

    {{-- Funnel bars --}}
    <div class="rounded-xl overflow-hidden" style="background:var(--bg-2); border:1px solid var(--line)">
        <div class="px-5 py-4" style="border-bottom:1px solid var(--line)">
            <span class="text-xs font-bold uppercase tracking-widest" style="color:var(--dim); font-family:JetBrainsMono,monospace">
                Étapes du funnel
            </span>
        </div>
        <div class="divide-y" style="border-color:var(--line)">
            @foreach($funnel as $step)
            @php $w = max($step['pct_top'], 1); @endphp
            <div class="px-5 py-4 flex items-center gap-4">
                <div class="w-48 shrink-0">
                    <span class="text-sm font-semibold" style="color:var(--ink); font-family:SpaceGrotesk,sans-serif">
                        {{ $step['label'] }}
                    </span>
                </div>
                <div class="flex-1 flex items-center gap-3">
                    <div class="flex-1 h-5 rounded overflow-hidden" style="background:var(--bg-3)">
                        <div class="h-full rounded transition-all"
                             style="width:{{ $w }}%; background: {{ $w >= 50 ? 'var(--acc)' : ($w >= 20 ? '#f5a623' : '#ff5b3a') }}; opacity:0.85">
                        </div>
                    </div>
                    <span class="w-14 text-right text-sm font-bold font-mono" style="color:var(--ink)">
                        {{ number_format($step['count']) }}
                    </span>
                    <span class="w-12 text-right text-xs font-mono" style="color:var(--dim)">
                        {{ $step['pct_top'] }}%
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Tendance 7j --}}
    <div class="rounded-xl overflow-hidden" style="background:var(--bg-2); border:1px solid var(--line)">
        <div class="px-5 py-4" style="border-bottom:1px solid var(--line)">
            <span class="text-xs font-bold uppercase tracking-widest" style="color:var(--dim); font-family:JetBrainsMono,monospace">
                Tendance 7 jours
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="border-bottom:1px solid var(--line)">
                        <th class="px-5 py-3 text-left text-xs font-semibold" style="color:var(--dim)">Date</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold" style="color:var(--dim)">App ouverte</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold" style="color:var(--acc)">Inscriptions</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold" style="color:#f5a623">Premium activé</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color:var(--line)">
                    @foreach($dailyTrend as $day)
                    <tr>
                        <td class="px-5 py-3 font-mono text-xs" style="color:var(--dim)">{{ $day['date'] }}</td>
                        <td class="px-5 py-3 text-right font-mono font-bold" style="color:var(--ink)">{{ $day['opens'] }}</td>
                        <td class="px-5 py-3 text-right font-mono font-bold" style="color:var(--acc)">{{ $day['signups'] }}</td>
                        <td class="px-5 py-3 text-right font-mono font-bold" style="color:#f5a623">{{ $day['subs'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
