@forelse ($schedules as $index => $schedule)
<tr>
    <td>{{ $schedules->firstItem() + $index }}</td>
    <td class="font-medium">
        {{ $schedule->student ? $schedule->student->name : ($schedule->kelas ? $schedule->kelas->name : 'Semua Siswa') }}
    </td>
    <td>
        {{ $schedule->student ? ($schedule->student->class->first()->name ?? '-') : ($schedule->kelas ? $schedule->kelas->name : '-') }}
    </td>
    <td>{{ \Carbon\Carbon::parse($schedule->scheduled_at)->locale('id')->isoFormat('DD MMMM Y') }}</td>
    <td>{{ \Carbon\Carbon::parse($schedule->scheduled_at)->format('H.i') }}</td>
    <td>
        @if ($schedule->status === 'finished')
            <span class="badge-selesai">Selesai</span>
        @else
            <span class="badge-aktif">Aktif</span>
        @endif
    </td>
    <td>
         @if ($schedule->status !== 'finished')
            <button onclick='openEditModal(@json($schedule))' class="btn-edit-square" title="Edit Jadwal">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
            </button>
        @else
            <span class="text-gray-400">-</span>
        @endif
    </td>
</tr>
@empty
<tr>
    <td colspan="7" class="py-10 text-gray-500 text-center">Belum ada jadwal.</td>
</tr>
@endforelse
