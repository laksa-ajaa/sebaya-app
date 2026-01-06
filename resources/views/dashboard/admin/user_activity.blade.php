@extends('layouts.app')

@section('title', 'User Activity Statistics')

@section('content')
  <div class="px-6 py-6 bg-blue-100 min-h-screen">
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-[#010E82]">User Activity Statistics</h1>
      <p class="text-gray-600 mt-1">Monitor penggunaan fitur aplikasi oleh setiap user</p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
      <div class="bg-white rounded-[15px] p-6" style="box-shadow: 1px 2px 2px 0px #00000040;">
        <div class="text-center">
          <p class="text-gray-600 text-sm mb-2">Total Users</p>
          <p class="text-3xl font-bold text-[#010E82]">{{ $users->total() }}</p>
        </div>
      </div>
      <div class="bg-white rounded-[15px] p-6" style="box-shadow: 1px 2px 2px 0px #00000040;">
        <div class="text-center">
          <p class="text-gray-600 text-sm mb-2">Total Mood Checks</p>
          <p class="text-3xl font-bold text-green-600">{{ $users->sum('mood_checks_count') }}</p>
        </div>
      </div>
      <div class="bg-white rounded-[15px] p-6" style="box-shadow: 1px 2px 2px 0px #00000040;">
        <div class="text-center">
          <p class="text-gray-600 text-sm mb-2">Total Journals</p>
          <p class="text-3xl font-bold text-blue-600">{{ $users->sum('journals_count') }}</p>
        </div>
      </div>
      <div class="bg-white rounded-[15px] p-6" style="box-shadow: 1px 2px 2px 0px #00000040;">
        <div class="text-center">
          <p class="text-gray-600 text-sm mb-2">Total Screenings</p>
          <p class="text-3xl font-bold text-purple-600">{{ $users->sum('screening_sessions_count') }}</p>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-[15px] mb-6" style="box-shadow: 1px 2px 2px 0px #00000040; border: 1px solid #B3b7da;">
      <!-- Header Filter -->
      <div style="background-color: #5087E4;" class="px-6 py-3 rounded-t-[15px]">
        <h3 class="text-white font-semibold">Filter & Search</h3>
      </div>

      <!-- Form Filter -->
      <form method="GET" class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Search User -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Search User</label>
            <input type="text"
              class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:ring-[#010E82] focus:border-transparent"
              style="border: 1px solid #010E82;" id="search" name="search" value="{{ $search }}"
              placeholder="Nama atau Email">
          </div>

          <!-- Role -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
            <select class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:ring-[#010E82] focus:border-transparent"
              style="border: 1px solid #010E82;" id="role" name="role">
              <option value="all" {{ $role === 'all' ? 'selected' : '' }}>-Pilih-</option>
              <option value="umum" {{ $role === 'umum' ? 'selected' : '' }}>Umum</option>
              <option value="student" {{ $role === 'student' ? 'selected' : '' }}>Siswa</option>
              <option value="teacher" {{ $role === 'teacher' ? 'selected' : '' }}>Guru</option>
              <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Admin</option>
            </select>
          </div>

          <!-- Per Page -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Per Page</label>
            <select class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:ring-[#010E82] focus:border-transparent"
              style="border: 1px solid #010E82;" id="per_page" name="per_page">
              <option value="15" {{ $perPage == 15 ? 'selected' : '' }}>15</option>
              <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
              <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
            </select>
          </div>
        </div>

        <div class="flex gap-3 mt-4">
          <button type="submit"
            class="px-6 py-2 bg-[#010E82] text-white rounded-lg hover:bg-[#0B3BAA] transition-colors">
            Apply Filters
          </button>
          <a href="{{ route('admin.user-activity') }}"
            class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors flex items-center justify-center">
            Clear
          </a>
          <button class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
            onclick="exportToCSV()">
            Export CSV
          </button>
        </div>
      </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-[15px] p-6" style="box-shadow: 1px 2px 2px 0px #00000040;">
      <!-- Dropdown Limit di atas tabel -->
      <div class="mb-4 flex items-center gap-2">
        <span class="text-sm text-gray-600">Tampilkan:</span>
        <select id="perPageSelect" onchange="changePerPage(this.value)"
          class="px-3 py-1 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#010E82] focus:border-transparent">
          <option value="15" {{ $perPage == 15 ? 'selected' : '' }}>15</option>
          <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
          <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
        </select>
        <span class="text-sm text-gray-600">per halaman</span>
      </div>

      <div class="overflow-x-auto rounded-[15px]" style="border: 1px solid #B3b7da; position: relative;">
        <table class="min-w-full" style="border-collapse: separate; border-spacing: 0;">
          <thead style="background-color: #5087E4;">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider"
                style="border-right: 1px solid #B3b7da; border-bottom: 1px solid #B3b7da;">
                User
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider"
                style="border-right: 1px solid #B3b7da; border-bottom: 1px solid #B3b7da;">
                Role
              </th>
              <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider"
                style="border-right: 1px solid #B3b7da; border-bottom: 1px solid #B3b7da;">
                Mood
              </th>
              <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider"
                style="border-right: 1px solid #B3b7da; border-bottom: 1px solid #B3b7da;">
                Journals
              </th>
              <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider"
                style="border-right: 1px solid #B3b7da; border-bottom: 1px solid #B3b7da;">
                Todo
              </th>
              <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider"
                style="border-right: 1px solid #B3b7da; border-bottom: 1px solid #B3b7da;">
                Habits
              </th>
              <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider"
                style="border-right: 1px solid #B3b7da; border-bottom: 1px solid #B3b7da;">
                Screening
              </th>
              <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider"
                style="border-right: 1px solid #B3b7da; border-bottom: 1px solid #B3b7da;">
                Chat
              </th>
              <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider"
                style="border-bottom: 1px solid #B3b7da;">
                Total
              </th>
            </tr>
          </thead>
          <tbody class="bg-white">
            @forelse($users as $user)
              <tr class="hover:bg-gray-50" style="border-bottom: 1px solid #B3b7da;">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                  style="border-right: 1px solid #B3b7da;">
                  <div class="flex items-center">
                    <div
                      class="w-10 h-10 @if ($user->role === 'admin') bg-purple-500 @elseif($user->role === 'teacher') bg-green-500 @elseif($user->role === 'user' && $user->class->count() > 0) bg-blue-500 @else bg-gray-500 @endif text-white rounded-full flex items-center justify-center font-bold text-sm mr-3">
                      {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div>
                      <div class="font-bold">{{ $user->name }}</div>
                      <div class="text-gray-500 text-sm">{{ $user->email }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap" style="border-right: 1px solid #B3b7da;">
                  @if ($user->role === 'admin')
                    <span
                      class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                      Admin
                    </span>
                  @elseif($user->role === 'teacher')
                    <span
                      class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                      Guru
                    </span>
                  @elseif($user->role === 'user' && $user->class->count() > 0)
                    <span
                      class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                      Siswa
                    </span>
                  @else
                    <span
                      class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                      Umum
                    </span>
                  @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center" style="border-right: 1px solid #B3b7da;">
                  <span
                    class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                    {{ $user->mood_checks_count }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center" style="border-right: 1px solid #B3b7da;">
                  <span
                    class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                    {{ $user->journals_count }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center" style="border-right: 1px solid #B3b7da;">
                  <span
                    class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                    {{ $user->todo_items_count }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center" style="border-right: 1px solid #B3b7da;">
                  <span
                    class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                    {{ $user->habits_count }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center" style="border-right: 1px solid #B3b7da;">
                  <span
                    class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">
                    {{ $user->screening_sessions_count }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center" style="border-right: 1px solid #B3b7da;">
                  <span
                    class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                    {{ $user->chat_messages_count }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                  @php
                    $total =
                        $user->mood_checks_count +
                        $user->journals_count +
                        $user->todo_items_count +
                        $user->habits_count +
                        $user->screening_sessions_count +
                        $user->chat_messages_count;
                  @endphp
                  <span
                    class="px-3 py-1 inline-flex text-sm leading-5 font-bold rounded-full bg-green-100 text-green-800">
                    {{ $total }}
                  </span>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="9" class="px-6 py-4 text-center text-sm text-gray-500">
                  No users found
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <!-- Footer dengan Info Data dan Pagination -->
      <div class="flex justify-between items-center mt-6 pt-4 border-t border-gray-200">
        <!-- Kiri: Info Data -->
        <div class="text-sm text-gray-600">
          Menampilkan {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} data
        </div>

        <!-- Kanan: Pagination -->
        @if ($users->hasPages())
          <div>
            {{ $users->appends(request()->query())->links() }}
          </div>
        @endif
      </div>
    </div>
  </div>

  <script>
    function changePerPage(value) {
      const url = new URL(window.location.href);
      url.searchParams.set('per_page', value);
      window.location.href = url.toString();
    }

    function exportToCSV() {
      const table = document.querySelector('table');
      let csv = [];

      // Get headers
      const headers = [];
      table.querySelectorAll('thead th').forEach(th => {
        const text = th.textContent.trim().replace(/^\s*|\s*$/g, '');
        headers.push(text);
      });
      csv.push(headers.join(','));

      // Get rows
      table.querySelectorAll('tbody tr').forEach(tr => {
        if (!tr.querySelector('td[colspan]')) {
          const row = [];
          tr.querySelectorAll('td').forEach(td => {
            let text = td.textContent.trim();
            // Remove avatar and keep name and email
            if (td.querySelector('.flex.items-center')) {
              const name = td.querySelector('.font-bold')?.textContent || '';
              const email = td.querySelector('.text-gray-500')?.textContent || '';
              text = name + ' ' + email;
            }
            // Remove badges and keep numbers
            text = text.replace(/[^\d\s@.-]/g, '').trim();
            row.push('"' + text + '"');
          });
          csv.push(row.join(','));
        }
      });

      // Download CSV
      const csvContent = csv.join('\n');
      const blob = new Blob([csvContent], {
        type: 'text/csv;charset=utf-8;'
      });
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'user_activity_' + new Date().toISOString().split('T')[0] + '.csv';
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      window.URL.revokeObjectURL(url);
    }
  </script>
@endsection
