@extends('layouts.app')

@section('title', 'Manajemen Artikel')

@section('content')
  <div class="px-6 py-6 bg-blue-100 min-h-screen">
    <!-- Header -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-bold text-[#010E82]">Manajemen Artikel</h1>
        <p class="text-gray-600 mt-1">Kelola konten edukasi dan informasi untuk siswa</p>
      </div>
      <a href="{{ route('admin.articles.create') }}"
        class="inline-flex items-center justify-center px-6 py-2.5 bg-[#010E82] text-white rounded-lg hover:bg-[#0B3BAA] transition-all shadow-md font-semibold text-sm">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Tambah Artikel
      </a>
    </div>

    <!-- Success Message -->
    @if (session('success'))
      <div class="mb-6 bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-xl flex items-center shadow-sm" role="alert">
        <svg class="w-5 h-5 mr-3 text-green-500" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
        </svg>
        <span class="text-sm font-medium">{{ session('success') }}</span>
      </div>
    @endif

    <!-- Content Card -->
    <div class="bg-white rounded-[15px] overflow-hidden" style="box-shadow: 1px 2px 2px 0px #00000040;">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-blue-50/50">
              <th scope="col" class="px-6 py-4 text-xs font-bold text-[#010E82] uppercase tracking-wider border-b border-gray-100">
                ID
              </th>
              <th scope="col" class="px-6 py-4 text-xs font-bold text-[#010E82] uppercase tracking-wider border-b border-gray-100">
                Judul Artikel
              </th>
              <th scope="col" class="px-6 py-4 text-xs font-bold text-[#010E82] uppercase tracking-wider border-b border-gray-100">
                Preview Konten
              </th>
              <th scope="col" class="px-6 py-4 text-xs font-bold text-[#010E82] uppercase tracking-wider border-b border-gray-100">
                Tgl Terbit
              </th>
              <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-[#010E82] uppercase tracking-wider border-b border-gray-100">
                Aksi
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            @forelse($articles as $article)
              <tr class="hover:bg-blue-50/30 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-400">
                  #{{ $article->id }}
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm font-bold text-gray-900 line-clamp-1 truncate max-w-[250px]">{{ $article->title }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-500 line-clamp-1 truncate max-w-[350px]">
                    {{ Str::limit(strip_tags($article->content), 70) }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ $article->created_at->format('d/m/Y') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex items-center justify-center gap-1">
                    <a href="{{ route('admin.articles.show', $article) }}"
                      class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors" title="Lihat">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                      </svg>
                    </a>
                    <a href="{{ route('admin.articles.edit', $article) }}"
                      class="p-2 text-yellow-600 hover:bg-yellow-100 rounded-lg transition-colors" title="Edit">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                      </svg>
                    </a>
                    <form action="{{ route('admin.articles.destroy', $article) }}" method="POST" class="inline">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition-colors"
                        onclick="return confirm('Hapus artikel ini?')" title="Hapus">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="px-6 py-12 text-center text-gray-500 italic">
                  Belum ada artikel yang dibuat.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if ($articles->hasPages())
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
          {{ $articles->links() }}
        </div>
      @endif
    </div>
  </div>
@endsection
