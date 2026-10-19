<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                <span class="text-xs font-medium text-gray-500">Total Lembaga</span>
                <p class="text-2xl font-bold text-amber-600">{{ number_format($summaryMetrics['total_schools'] ?? 0) }}</p>
            </div>
            <div class="p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                <span class="text-xs font-medium text-gray-500">Total Guru</span>
                <p class="text-2xl font-bold text-blue-600">{{ number_format($summaryMetrics['total_teachers'] ?? 0) }}</p>
            </div>
            <div class="p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                <span class="text-xs font-medium text-gray-500">Total Siswa</span>
                <p class="text-2xl font-bold text-emerald-600">{{ number_format($summaryMetrics['total_students'] ?? 0) }}</p>
            </div>
            <div class="p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                <span class="text-xs font-medium text-gray-500">Sesi Game Dimainkan</span>
                <p class="text-2xl font-bold text-purple-600">{{ number_format($summaryMetrics['total_sessions'] ?? 0) }}</p>
            </div>
            <div class="p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                <span class="text-xs font-medium text-gray-500">Total Akumulasi XP</span>
                <p class="text-2xl font-bold text-indigo-600">{{ number_format($summaryMetrics['total_xp'] ?? 0) }}</p>
            </div>
        </div>

        <!-- Action Header -->
        <div class="flex items-center justify-between pt-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Statistik Partisipasi per Lembaga Sekolah</h3>
                <p class="text-sm text-gray-500">Performa adopsi game edukasi dan keaktifan murid di masing-masing sekolah.</p>
            </div>
            <x-filament::button wire:click="exportCsv" color="success" icon="heroicon-m-arrow-down-tray">
                Ekspor Laporan (CSV)
            </x-filament::button>
        </div>

        <!-- Schools Data Table -->
        <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
            <table class="w-full text-left text-sm text-gray-700 dark:text-gray-300">
                <thead class="bg-gray-50 dark:bg-gray-900 text-xs uppercase font-semibold text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Nama Sekolah</th>
                        <th class="px-4 py-3">Jenjang</th>
                        <th class="px-4 py-3">Lisensi</th>
                        <th class="px-4 py-3 text-center">Kelas</th>
                        <th class="px-4 py-3 text-center">Siswa</th>
                        <th class="px-4 py-3 text-center">Sesi Game</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse ($schoolsData as $school)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-750">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $school['nama'] }}</td>
                            <td class="px-4 py-3"><span class="px-2 py-0.5 text-xs rounded bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">{{ $school['jenjang'] }}</span></td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 text-xs rounded font-medium {{ $school['status_lisensi'] === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ ucfirst($school['status_lisensi']) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">{{ number_format($school['classes_count']) }}</td>
                            <td class="px-4 py-3 text-center">{{ number_format($school['students_count']) }}</td>
                            <td class="px-4 py-3 text-center font-bold text-amber-600">{{ number_format($school['sessions_count']) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-gray-500">Belum ada data sekolah terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
