<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <span>🌐 Quotas API — {{ $today }}</span>
            </div>
        </x-slot>

        {{-- Alerte globale --------------------------------------------------}}
        @if($globalAlert)
            <div class="mb-4 rounded-lg p-4 flex items-start gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                <span class="text-xl leading-none">{{ $globalAlert['icon'] }}</span>
                <p class="text-sm text-red-800 dark:text-red-200">{{ $globalAlert['message'] }}</p>
            </div>
        @endif

        {{-- Barres de progression par provider ------------------------------}}
        <div class="space-y-4">
            @foreach($providers as $key => $p)
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ $p['icon'] }} {{ $p['label'] }}
                        </span>
                        <div class="flex items-center gap-3">
                            @if($p['last_used'])
                                <span class="text-xs text-gray-400 dark:text-gray-500">dernier appel {{ $p['last_used'] }}</span>
                            @endif
                            @if($p['unlimited'])
                                <span class="text-xs font-bold text-green-600 dark:text-green-400">illimité</span>
                            @else
                                <span class="text-sm font-bold
                                    @if($p['status'] === 'critical') text-red-600 dark:text-red-400
                                    @elseif($p['status'] === 'warning') text-yellow-600 dark:text-yellow-400
                                    @else text-green-600 dark:text-green-400 @endif">
                                    {{ $p['remaining'] }} / {{ $p['limit'] }} restantes
                                </span>
                            @endif
                        </div>
                    </div>

                    @if(!$p['unlimited'])
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="h-2 rounded-full transition-all
                                @if($p['status'] === 'critical') bg-red-500
                                @elseif($p['status'] === 'warning') bg-yellow-500
                                @else bg-green-500 @endif"
                                style="width: {{ min($p['percentage'], 100) }}%">
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ $p['used'] }} utilisées ({{ $p['percentage'] }}%)
                        </p>
                    @else
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $p['used'] }} appels aujourd'hui — pas de limite quotidienne
                        </p>
                    @endif
                </div>
            @endforeach
        </div>

    </x-filament::section>
</x-filament-widgets::widget>
