<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex items-center justify-end gap-x-3">
            <x-filament::button type="submit" color="primary">
                Simpan Konfigurasi Pengaturan
            </x-filament::button>
        </div>
    </form>

    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-800 space-y-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Operasional & Pemeliharaan Shared Hosting</h3>
        <p class="text-sm text-gray-500">Alat bantu operasional server untuk membersihkan cache dan memicu pencadangan mandiri.</p>

        <div class="flex flex-wrap gap-4">
            <x-filament::button wire:click="clearSystemCache" color="warning" icon="heroicon-m-trash">
                Bersihkan Cache Aplikasi (Optimize Clear)
            </x-filament::button>

            <x-filament::button wire:click="triggerDatabaseBackup" color="info" icon="heroicon-m-circle-stack">
                Picu Cadangan Database Sekarang
            </x-filament::button>
        </div>

        <div class="mt-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Penampil Log Aplikasi (50 Baris Terakhir)</span>
                <x-filament::button wire:click="loadRecentLog" size="xs" color="gray" icon="heroicon-m-arrow-path">
                    Segarkan Log
                </x-filament::button>
            </div>
            <pre class="p-4 bg-gray-900 text-gray-100 rounded-lg text-xs overflow-x-auto max-h-96 font-mono">{{ $recentLog }}</pre>
        </div>
    </div>
</x-filament-panels::page>
