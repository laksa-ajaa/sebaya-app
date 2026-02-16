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

        return ApiResponse::success($article, 'Detail artikel berhasil diambil.');
    }
}
