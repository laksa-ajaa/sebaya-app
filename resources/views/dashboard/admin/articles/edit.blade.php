@extends('layouts.app')

@section('title', 'Edit Artikel')

@section('content')
  <div class="px-6 py-8 bg-blue-100 min-h-screen">
    <!-- Breadcrumbs/Back Button -->
    <div class="max-w-5xl mx-auto mb-6">
      <a href="{{ route('admin.articles.index') }}"
        class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-[#010E82] transition-colors">
        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
        Kembali ke Daftar Artikel
      </a>
    </div>

    <div class="max-w-5xl mx-auto">
      <div class="bg-white rounded-[20px] overflow-hidden" style="box-shadow: 1px 2px 2px 0px #00000040; border: 1px solid #E5E7EB;">
        <!-- Header -->
        <div class="px-8 py-6 border-b border-gray-100 bg-white flex justify-between items-center">
          <div>
            <h1 class="text-2xl font-bold text-[#010E82]">Edit Artikel</h1>
            <p class="text-gray-500 text-sm mt-1">Perbarui konten artikel agar tetap relevan dan akurat.</p>
          </div>
          <div class="hidden sm:block">
            <span class="px-3 py-1 bg-blue-50 text-blue-600 text-xs font-bold rounded-full uppercase tracking-wider">Mode Edit</span>
          </div>
        </div>

        <form action="{{ route('admin.articles.update', $article) }}" method="POST" enctype="multipart/form-data" class="p-8">
          @csrf
          @method('PUT')

          <!-- Judul -->
          <div class="mb-6">
            <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">
              Judul Artikel <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <input type="text" name="title" id="title" value="{{ old('title', $article->title) }}"
                placeholder="Masukkan judul artikel..."
                class="block w-full px-4 py-3 rounded-xl border-gray-200 focus:ring-2 focus:ring-[#010E82] focus:border-transparent transition-all @error('title') border-red-300 @enderror"
                required>
            </div>
            @error('title')
              <p class="mt-2 text-sm text-red-600 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
                {{ $message }}
              </p>
            @enderror
          </div>

          <!-- Thumbnail -->
          <div class="mb-6 hidden">
            <label for="thumbnail" class="block text-sm font-semibold text-gray-700 mb-2">
              Thumbnail Artikel (Opsional)
            </label>
            <div class="relative">
              <input type="file" name="thumbnail" id="thumbnail" accept="image/png, image/jpeg, image/jpg, image/webp"
                class="block w-full px-4 py-3 bg-white rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#010E82] focus:border-transparent transition-all @error('thumbnail') border-red-300 @enderror"
                onchange="previewImage(event)">
            </div>
            
            <div class="mt-3 {{ $article->thumbnail ? '' : 'hidden' }}" id="thumbnail-preview-container">
              <p class="text-xs text-gray-500 mb-1">Preview Thumbnail Saat Ini:</p>
              <img id="thumbnail-preview" src="{{ $article->thumbnail ? Storage::url($article->thumbnail) : '#' }}" alt="Preview Thumbnail" class="w-full max-w-sm rounded-[15px] border border-gray-200 shadow-sm object-cover" style="max-height: 250px;">
            </div>
            
            @error('thumbnail')
              <p class="mt-2 text-sm text-red-600 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
                {{ $message }}
              </p>
            @enderror
          </div>

          <!-- Konten -->
          <div class="mb-8">
            <label for="content" class="block text-sm font-semibold text-gray-700 mb-2">
              Konten Artikel <span class="text-red-500">*</span>
            </label>
            <div class="editor-container">
              <textarea name="content" id="content">{{ old('content', $article->content) }}</textarea>
            </div>
            @error('content')
              <p class="mt-2 text-sm text-red-600 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
                {{ $message }}
              </p>
            @enderror
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100">
            <a href="{{ route('admin.articles.index') }}"
              class="px-6 py-2.5 text-sm font-semibold text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition-all">
              Batal
            </a>
            <button type="submit"
              class="px-8 py-2.5 text-sm font-semibold text-white bg-[#010E82] rounded-xl hover:bg-[#0B3BAA] shadow-lg shadow-blue-900/20 transition-all active:scale-95 flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
              </svg>
              Perbarui Artikel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <style>
    /* Premium CKEditor Customization */
    .ck-editor__editable_inline {
      min-height: 450px;
      padding: 0 1.5rem !important;
      font-size: 1rem;
      line-height: 1.6;
      color: #374151;
    }

    .ck.ck-editor__main>.ck-editor__editable:not(.ck-focused) {
      border-color: #E5E7EB !important;
      border-radius: 0 0 0.75rem 0.75rem !important;
    }

    .ck.ck-editor__main>.ck-editor__editable.ck-focused {
      border-color: #010E82 !important;
      box-shadow: none !important;
      border-radius: 0 0 0.75rem 0.75rem !important;
    }

    .ck.ck-toolbar {
      background-color: #F9FAFF !important;
      border-color: #E5E7EB !important;
      border-radius: 0.75rem 0.75rem 0 0 !important;
      padding: 0.5rem !important;
    }

    .ck.ck-button {
      border-radius: 0.5rem !important;
      cursor: pointer !important;
    }

    .ck.ck-button:hover {
      background-color: #E0E7FF !important;
    }

    .ck.ck-button.ck-on {
      background-color: #DBEAFE !important;
      color: #010E82 !important;
    }

    /* Heading Styles inside Editor */
    .ck-content h1 {
      font-size: 2.25rem !important;
      font-weight: 800 !important;
      color: #010E82 !important;
      margin-top: 2rem !important;
      margin-bottom: 1.5rem !important;
      display: block !important;
    }

    .ck-content h2 {
      font-size: 1.875rem !important;
      font-weight: 700 !important;
      color: #010E82 !important;
      margin-top: 1.5rem !important;
      margin-bottom: 1rem !important;
      display: block !important;
    }

    .ck-content h3 {
      font-size: 1.5rem !important;
      font-weight: 600 !important;
      color: #010E82 !important;
      margin-top: 1.25rem !important;
      margin-bottom: 0.75rem !important;
      display: block !important;
    }

    .ck-content h4 {
      font-size: 1.25rem !important;
      font-weight: 600 !important;
      color: #010E82 !important;
      margin-top: 1rem !important;
      margin-bottom: 0.5rem !important;
      display: block !important;
    }

    .ck-content ul {
      list-style-type: disc !important;
      padding-left: 0 !important;
      margin-bottom: 1.5rem !important;
      list-style-position: inside !important;
    }

    .ck-content ol {
      list-style-type: decimal !important;
      padding-left: 0 !important;
      margin-bottom: 1.5rem !important;
      list-style-position: inside !important;
    }

    .ck-content li {
      margin-bottom: 0.5rem !important;
    }

    /* Marker (Number/Bullet) follows heading style if present */
    .ck-content li:has(h1)::marker { font-size: 2.25rem !important; font-weight: 800 !important; color: #010E82 !important; }
    .ck-content li:has(h2)::marker { font-size: 1.875rem !important; font-weight: 700 !important; color: #010E82 !important; }
    .ck-content li:has(h3)::marker { font-size: 1.5rem !important; font-weight: 600 !important; color: #010E82 !important; }
    .ck-content li:has(h4)::marker { font-size: 1.25rem !important; font-weight: 600 !important; color: #010E82 !important; }

    /* Ensure marker color for all lists */
    .ck-content ul li::marker, .ck-content ol li::marker {
      color: #010E82 !important;
    }

    /* Support for Headings inside Lists (Inline) */
    .ck-content li h1, .ck-content li h2, .ck-content li h3, .ck-content li h4 {
      display: inline !important; /* Fixed: use inline for perfect alignment with inside markers */
      margin: 0 !important;
      padding: 0 !important;
      border: none !important;
    }

    .ck-content p {
      margin-bottom: 1rem !important;
    }

    .ck-content figcaption {
      margin-top: 0.75rem !important;
      padding: 0.5rem !important;
      font-size: 0.875rem !important;
      color: #6B7280 !important;
      font-style: italic !important;
      text-align: center !important;
      display: block !important;
    }
  </style>

  @vite(['resources/js/article-editor.js'])
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      if (window.initArticleEditor) {
        window.initArticleEditor('#content');
      }
    });

    function previewImage(event) {
      const container = document.getElementById('thumbnail-preview-container');
      const image = document.getElementById('thumbnail-preview');
      const file = event.target.files[0];
      
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          image.src = e.target.result;
          container.classList.remove('hidden');
        }
        reader.readAsDataURL(file);
      } else {
        // Jika tidak ada file baru terpilih, kita bisa menyembunyikan atau kembalikan ke awal
        // Namun karena ini page edit, jika user batalkan pilih file, lebih baik biarkan sesuai aslinya
@if(!$article->thumbnail)
        container.classList.add('hidden');
        image.src = '#';
@endif
      }
    }
  </script>
@endsection
