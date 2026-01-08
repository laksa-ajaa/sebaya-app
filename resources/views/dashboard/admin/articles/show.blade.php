@extends('layouts.app')

@section('title', 'Lihat Artikel')

@section('content')
  <div class="px-6 py-8 bg-blue-100 min-h-screen">
    <!-- Breadcrumbs -->
    <div class="max-w-5xl mx-auto mb-6">
      <a href="{{ route('admin.articles.index') }}"
        class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-[#010E82] transition-colors group">
        <svg class="w-5 h-5 mr-1 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
        Kembali ke Daftar Artikel
      </a>
    </div>

    <div class="max-w-5xl mx-auto">
      <article class="bg-white rounded-[20px] shadow-md overflow-hidden" style="box-shadow: 1px 2px 2px 0px #00000040; border: 1px solid #E5E7EB;">
        <!-- Article Header -->
        <div class="px-8 py-10 border-b border-gray-50 bg-white">
          <div class="flex items-center gap-2 mb-4">
            <span class="px-3 py-1 bg-blue-50 text-[#010E82] text-xs font-bold rounded-full uppercase tracking-wider">Artikel</span>
            <span class="text-gray-400 text-sm">•</span>
            <span class="text-gray-500 text-sm italic">{{ $article->created_at->format('d M Y') }}</span>
          </div>
          <h1 class="text-4xl font-extrabold text-[#010E82] leading-tight mb-4">
            {{ $article->title }}
          </h1>
          <div class="flex items-center text-sm text-white/80">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Estimasi baca: {{ ceil(str_word_count(strip_tags($article->content)) / 200) }} menit
          </div>
        </div>

        <!-- Article Content -->
        <div class="px-8 py-10">
          <div class="article-content ck-content prose prose-blue max-w-none">
            {!! $article->content !!}
          </div>
        </div>

        <!-- Article Footer -->
        <div class="px-8 py-6 bg-gray-50 border-t border-gray-100 flex justify-between items-center">
          <div class="text-sm text-gray-500">
            Terakhir diperbarui: {{ $article->updated_at->format('d M Y, H:i') }}
          </div>
          <div class="flex gap-2">
            <a href="{{ route('admin.articles.edit', $article) }}"
              class="px-5 py-2 text-sm font-semibold text-[#010E82] bg-white border border-[#010E82] rounded-xl hover:bg-blue-50 transition-all flex items-center">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
              </svg>
              Edit Artikel
            </a>
          </div>
        </div>
      </article>
    </div>
  </div>

  <style>
    /* Styling for Rendered Content Headings & Elements */
    .article-content {
      font-size: 1.125rem;
      line-height: 1.8;
      color: #374151;
    }

    /* CKEditor Image Resizing & Alignment */
    .article-content figure.image {
      margin: 2rem 0;
      display: table;
      clear: both;
      text-align: center;
      margin-left: auto;
      margin-right: auto;
    }

    .article-content figure.image img {
      display: block;
      margin: 0 auto;
      max-width: 100%;
      height: auto;
      border-radius: 0.75rem;
    }

    .article-content figure.image-style-side {
      float: right;
      margin-left: 1.5rem;
      max-width: 50%;
    }

    .article-content figure.image-style-block-align-left {
      float: left;
      margin-right: 1.5rem;
    }

    .article-content figure.image-style-block-align-right {
      float: right;
      margin-left: 1.5rem;
    }

    .article-content figure.image.image_resized {
      display: block;
      box-sizing: border-box;
    }

    .article-content figure.image.image_resized img {
      width: 100%;
    }

    .article-content figcaption {
      margin-top: 0.75rem;
      padding: 0.5rem 1rem;
      font-size: 0.875rem;
      line-height: 1.5;
      color: #6B7280; /* Gray-500 */
      font-style: italic;
      text-align: center;
      border-left: 2px solid #E5E7EB;
      margin-left: auto;
      margin-right: auto;
      display: block;
      max-width: 80%;
    }

    /* Handle Tailwind CSS Reset for CKEditor Content */
    .ck-content h1 { font-size: 2.25rem !important; font-weight: 800 !important; color: #010E82 !important; margin-top: 3rem !important; margin-bottom: 1.5rem !important; display: block !important; }
    .ck-content h2 { font-size: 1.875rem !important; font-weight: 700 !important; color: #010E82 !important; margin-top: 2.5rem !important; margin-bottom: 1.25rem !important; border-bottom: 2px solid #F3F4F6 !important; padding-bottom: 0.5rem !important; display: block !important; }
    .ck-content h3 { font-size: 1.5rem !important; font-weight: 600 !important; color: #010E82 !important; margin-top: 2rem !important; margin-bottom: 1rem !important; display: block !important; }
    .ck-content h4 { font-size: 1.25rem !important; font-weight: 600 !important; color: #010E82 !important; margin-top: 1.5rem !important; margin-bottom: 0.75rem !important; display: block !important; }

    .ck-content ul { list-style-type: disc !important; list-style-position: inside !important; padding-left: 0 !important; margin-bottom: 1.5rem !important; display: block !important; }
    .ck-content ol { list-style-type: decimal !important; list-style-position: inside !important; padding-left: 0 !important; margin-bottom: 1.5rem !important; display: block !important; }
    .ck-content li { margin-bottom: 0.5rem !important; }

    /* Marker (Number/Bullet) follows heading style if present */
    .ck-content li:has(h1)::marker { font-size: 2.25rem !important; font-weight: 800 !important; color: #010E82 !important; }
    .ck-content li:has(h2)::marker { font-size: 1.875rem !important; font-weight: 700 !important; color: #010E82 !important; }
    .ck-content li:has(h3)::marker { font-size: 1.5rem !important; font-weight: 600 !important; color: #010E82 !important; }
    .ck-content li:has(h4)::marker { font-size: 1.25rem !important; font-weight: 600 !important; color: #010E82 !important; }

    /* Ensure marker color for all lists */
    .ck-content ul li::marker, .ck-content ol li::marker { color: #010E82 !important; }

    /* Support for Headings inside Lists (Inline) */
    .ck-content li h1, .ck-content li h2, .ck-content li h3, .ck-content li h4 {
      display: inline !important;
      margin: 0 !important;
      padding: 0 !important;
      border: none !important;
    }

    .ck-content p { margin-bottom: 1.5rem !important; display: block !important; }

    /* Tables */
    .ck-content table {
      width: 100% !important;
      border-collapse: collapse !important;
      margin: 2rem 0 !important;
      border: 1px solid #E5E7EB !important;
    }

    .ck-content th, .ck-content td {
      border: 1px solid #E5E7EB !important;
      padding: 0.75rem 1rem !important;
      text-align: left !important;
    }

    .ck-content th {
      background-color: #F9FAFF !important;
      font-weight: 600 !important;
      color: #010E82 !important;
    }

    /* CKEditor 5 Modern Classes */
    .article-content .ck-image_resized {
      display: block;
      margin-left: auto;
      margin-right: auto;
    }

    .article-content .image-style-side,
    .article-content .image-style-align-right {
      float: right;
      margin-left: 1.5rem;
      max-width: 50%;
    }

    .article-content .image-style-align-left {
      float: left;
      margin-right: 1.5rem;
      max-width: 50%;
    }

    .article-content .image-inline {
      display: inline-block;
      vertical-align: bottom;
    }

    /* Clearfix for floating images */
    .article-content::after {
      content: "";
      display: table;
      clear: both;
    }
  </style>
@endsection
