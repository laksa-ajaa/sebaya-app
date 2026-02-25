<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    /**
     * Display a listing of the articles.
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $search = $request->input('search');

        $articles = Article::query()
            ->when($search, function ($query, $search) {
                return $query->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($limit);

        $articles->getCollection()->transform(function ($article) {
            $article->thumbnail_url = $article->thumbnail ? url('storage/' . $article->thumbnail) : null;
            return $article;
        });

        return ApiResponse::success($articles, 'Daftar artikel berhasil diambil.');
    }

    /**
     * Display the specified article.
     */
    public function show(string $slug)
    {
        $article = Article::where('slug', $slug)->first();

        if (!$article) {
            return ApiResponse::error('Artikel tidak ditemukan.', null, 404);
        }
        
        $article->thumbnail_url = $article->thumbnail ? url('storage/' . $article->thumbnail) : null;

        // Generate full HTML template including styling targeted for mobile WebView
        $article->webview_content = $this->generateWebViewContent($article);

        return ApiResponse::success($article, 'Detail artikel berhasil diambil.');
    }

    /**
     * Generate HTML layout with embedded CSS for Mobile WebView.
     */
    private function generateWebViewContent(Article $article): string
    {
        $date = $article->created_at ? $article->created_at->format('d M Y') : '';

        $css = "
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
                margin: 0;
                padding: 16px;
                background-color: #ffffff;
            }
            .article-content {
                font-size: 1rem;
                line-height: 1.7;
                color: #374151;
            }
            .article-content img {
                max-width: 100%;
                height: auto;
                border-radius: 0.5rem;
            }
            .ck-content h1 { font-size: 1.75rem !important; font-weight: 800 !important; color: #010E82 !important; margin-top: 1.5rem !important; margin-bottom: 1rem !important; line-height: 1.3 !important; }
            .ck-content h2 { font-size: 1.5rem !important; font-weight: 700 !important; color: #010E82 !important; margin-top: 1.25rem !important; margin-bottom: 0.75rem !important; border-bottom: 1px solid #F3F4F6 !important; padding-bottom: 0.5rem !important; }
            .ck-content h3 { font-size: 1.25rem !important; font-weight: 600 !important; color: #010E82 !important; margin-top: 1rem !important; margin-bottom: 0.5rem !important; }
            .ck-content p { margin-bottom: 1rem !important; }
            .ck-content ul { list-style-type: disc !important; padding-left: 1.25rem !important; margin-bottom: 1rem !important; }
            .ck-content ol { list-style-type: decimal !important; padding-left: 1.25rem !important; margin-bottom: 1rem !important; }
            .ck-content li { margin-bottom: 0.5rem !important; }
            .ck-content ul li::marker, .ck-content ol li::marker { color: #010E82 !important; }
            .ck-content table { width: 100% !important; border-collapse: collapse !important; margin: 1rem 0 !important; font-size: 0.875rem !important; }
            .ck-content th, .ck-content td { border: 1px solid #E5E7EB !important; padding: 0.5rem !important; }
            .ck-content th { background-color: #F9FAFF !important; font-weight: 600 !important; color: #010E82 !important; }
            .ck-content figure.image { margin: 1.25rem 0; text-align: center; display: block; }
            .ck-content figure.image.image-style-align-left { margin-right: auto; margin-left: 0; text-align: left; }
            .ck-content figure.image.image-style-align-center { margin-left: auto; margin-right: auto; text-align: center; }
            .ck-content figure.image.image-style-align-right { margin-left: auto; margin-right: 0; text-align: right; }
            .ck-content figure.image img { margin: 0 auto; display: block; max-width: 100%; height: auto; }
            .ck-content p[style*='text-align: center'] img, .ck-content p[style*='text-align:center'] img { margin: 0 auto; display: block; }
            .ck-content figcaption { margin-top: 0.5rem; font-size: 0.75rem; color: #6B7280; font-style: italic; text-align: center; }
            
            /* Header Specific Styling */
            .mobile-header {
                margin-bottom: 1.25rem;
                padding-bottom: 1rem;
                border-bottom: 1px solid #E5E7EB;
            }
            .mobile-header-meta {
                display: flex;
                align-items: center;
                gap: 8px;
                margin-bottom: 12px;
            }
            .mobile-header-badge {
                padding: 4px 12px;
                background-color: #EFF6FF;
                color: #010E82;
                font-size: 11px;
                font-weight: bold;
                border-radius: 9999px;
                text-transform: uppercase;
                letter-spacing: 0.05em;
            }
            .mobile-header-title {
                font-size: 24px;
                font-weight: 800;
                color: #010E82;
                margin: 0;
                line-height: 1.3;
            }
            .meta-dot { color: #9CA3AF; font-size: 12px; }
            .meta-date { color: #6B7280; font-size: 13px; font-style: italic; }
        </style>";

        $header = "
        <div class='mobile-header'>
            <div class='mobile-header-meta'>
                <span class='mobile-header-badge'>Artikel</span>
                <span class='meta-dot'>•</span>
                <span class='meta-date'>{$date}</span>
            </div>
            <h1 class='mobile-header-title'>{$article->title}</h1>
        </div>";

        // Pastikan path image relatif seperti src="/storage/..." diubah menjadi absolute url (misal: https://domainanda.com/storage/...)
        $baseUrl = rtrim(config('app.url'), '/');
        $contentWithAbsoluteUrls = preg_replace('/src="\/([^"]+)"/', 'src="' . $baseUrl . '/$1"', $article->content);

        $html = "<!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no' />
            {$css}
        </head>
        <body>
            {$header}
            <div class='article-content ck-content'>
                {$contentWithAbsoluteUrls}
            </div>
        </body>
        </html>";

        return $html;
    }
}
