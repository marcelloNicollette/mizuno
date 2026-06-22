<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function index($slug)
    {
        $blogs = $this->baseQuery()
            ->orderByRaw('COALESCE(material_date, publish_at, created_at) DESC')
            ->get();

        $anos = $blogs->pluck('material_date')
            ->filter()
            ->map(fn($date) => Carbon::parse($date)->year)
            ->unique()
            ->sortDesc()
            ->values();

        return view('user.blog', [
            'slug' => $slug,
            'blogs' => $blogs,
            'anos' => $anos,
        ]);
    }

    public function show($slug, Blog $blog)
    {
        abort_unless($this->baseQuery()->whereKey($blog->id)->exists(), 404);

        $relatedBlogs = $this->baseQuery()
            ->whereKeyNot($blog->id)
            ->orderByRaw('COALESCE(material_date, publish_at, created_at) DESC')
            ->limit(4)
            ->get();

        return view('user.blog-detalhe', [
            'slug' => $slug,
            'blog' => $blog,
            'relatedBlogs' => $relatedBlogs,
        ]);
    }

    private function baseQuery()
    {
        $now = Carbon::now();

        return Blog::query()
            ->forUser(Auth::user())
            ->where('active', true)
            ->where('status', true)
            ->where(function ($query) use ($now) {
                $query->whereNull('publish_at')
                    ->orWhere('publish_at', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('unpublish_at')
                    ->orWhere('unpublish_at', '>=', $now);
            });
    }

    public static function materialDateLabel(Blog $blog): string
    {
        $date = $blog->material_date ?? $blog->publish_at ?? $blog->created_at;

        if (!$date) {
            return '';
        }

        return Str::ucfirst(Carbon::parse($date)->locale('pt_BR')->translatedFormat('M/Y'));
    }
}
