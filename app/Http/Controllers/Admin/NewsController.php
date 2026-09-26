<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class NewsController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->query('cari');
        $categoryId = $request->query('kategori');
        $status = $request->query('status');

        $query = Article::with('category')->latest();

        if ($search) {
            $search = str_replace(['%', '_'], ['\\%', '\\_'], $search);
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        if ($categoryId && $categoryId !== 'semua') {
            $query->where('category_id', $categoryId);
        }

        if ($status && $status !== 'semua') {
            $query->where('status', $status);
        }

        $articles = $query->get();
        $categories = Category::all();

        return Inertia::render('Admin/News/Index', [
            'articles' => $articles,
            'categories' => $categories,
            'filters' => [
                'cari' => $search ?? '',
                'kategori' => $categoryId ?? 'semua',
                'status' => $status ?? 'semua',
            ],
        ]);
    }

    public function create(): Response
    {
        $categories = Category::all();

        return Inertia::render('Admin/News/Form', [
            'isEdit' => false,
            'article' => null,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'image_url' => ['nullable', 'string', 'max:1000'],
            'arabic_quote' => ['nullable', 'string'],
            'is_featured' => ['boolean'],
            'status' => ['required', 'in:published,draft'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image_url'] = $this->saveImageAsWebp($request->file('image'), 'images/news', 1200, $validated['title']);
        }

        unset($validated['image']);

        // Sanitize HTML content — allow only safe tags from TipTap editor
        $validated['content'] = strip_tags($validated['content'], [
            'p', 'br', 'strong', 'em', 'u', 'h2', 'h3', 'ul', 'ol', 'li',
            'a', 'img', 'figure', 'figcaption', 'blockquote', 'hr', 'div', 'span',
        ]);

        $validated['user_id'] = auth()->id();
        // Slug generation is handled by Article model boot event
        if ($validated['status'] === 'published') {
            $validated['published_at'] = now();
        }

        Article::create($validated);

        return redirect()->route('admin.news.index')->with('success', 'Berita berhasil diterbitkan!');
    }

    public function edit(Article $news): Response
    {
        $categories = Category::all();

        return Inertia::render('Admin/News/Form', [
            'isEdit' => true,
            'article' => $news->load('category'),
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, Article $news): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'image_url' => ['nullable', 'string', 'max:1000'],
            'arabic_quote' => ['nullable', 'string'],
            'is_featured' => ['boolean'],
            'status' => ['required', 'in:published,draft'],
        ]);

        if ($request->hasFile('image')) {
            // Safely delete old image only if it's a local storage path
            if ($news->image_url && str_starts_with($news->image_url, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $news->image_url);
                Storage::disk('public')->delete($oldPath);
            }

            $validated['image_url'] = $this->saveImageAsWebp($request->file('image'), 'images/news', 1200, $validated['title']);
        }

        unset($validated['image']);

        // Sanitize HTML content — allow only safe tags from TipTap editor
        $validated['content'] = strip_tags($validated['content'], [
            'p', 'br', 'strong', 'em', 'u', 'h2', 'h3', 'ul', 'ol', 'li',
            'a', 'img', 'figure', 'figcaption', 'blockquote', 'hr', 'div', 'span',
        ]);

        if ($news->status !== 'published' && $validated['status'] === 'published' && ! $news->published_at) {
            $validated['published_at'] = now();
        }

        $news->update($validated);

        return redirect()->route('admin.news.index')->with('success', 'Berita berhasil diperbarui!');
    }

    public function destroy(Article $news): RedirectResponse
    {
        // Safely delete image only if it's a local storage path
        if ($news->image_url && str_starts_with($news->image_url, '/storage/')) {
            $imagePath = str_replace('/storage/', '', $news->image_url);
            Storage::disk('public')->delete($imagePath);
        }

        $news->delete();

        return redirect()->route('admin.news.index')->with('success', 'Berita berhasil dihapus!');
    }

    public function togglePublish(Article $news): RedirectResponse
    {
        $newStatus = $news->status === 'published' ? 'draft' : 'published';
        $news->status = $newStatus;
        if ($newStatus === 'published' && ! $news->published_at) {
            $news->published_at = now();
        }
        $news->save();

        return back()->with('success', "Status berita diubah menjadi {$newStatus}!");
    }

    public function uploadContentImage(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        $url = $this->saveImageAsWebp($request->file('image'), 'images/news/content', 1400, 'content');

        return response()->json([
            'url' => $url,
        ]);
    }

    /**
     * Helper untuk memproses, mengompres, dan menyimpan gambar ke format WebP menggunakan GD bawaan PHP.
     */
    private function saveImageAsWebp($file, string $subDirectory, int $maxWidth = 1200, string $prefix = ''): string
    {
        $dir = storage_path('app/public/' . $subDirectory);
        if (! file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        $slugPrefix = $prefix ? Str::slug($prefix) . '-' : '';
        $filename = $slugPrefix . time() . '-' . Str::random(5) . '.webp';
        $targetPath = $dir . '/' . $filename;
        $pathname = $file->getPathname();

        $source = null;
        $mime = $file->getMimeType();
        $origName = strtolower($file->getClientOriginalName());

        try {
            if ($mime === 'image/jpeg' || str_ends_with($origName, '.jpg') || str_ends_with($origName, '.jpeg')) {
                $source = @imagecreatefromjpeg($pathname);
            } elseif ($mime === 'image/png' || str_ends_with($origName, '.png')) {
                $source = @imagecreatefrompng($pathname);
            } elseif ($mime === 'image/webp' || str_ends_with($origName, '.webp')) {
                $source = @imagecreatefromwebp($pathname);
            }
        } catch (\Throwable $e) {
            $source = null;
        }

        if ($source) {
            $origWidth = imagesx($source);
            $origHeight = imagesy($source);

            if ($origWidth > $maxWidth) {
                $newWidth = $maxWidth;
                $newHeight = (int) round(($origHeight / $origWidth) * $maxWidth);

                $target = imagecreatetruecolor($newWidth, $newHeight);
                imagealphablending($target, false);
                imagesavealpha($target, true);

                imagecopyresampled($target, $source, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
                imagewebp($target, $targetPath, 82);
                imagedestroy($target);
            } else {
                imagealphablending($source, false);
                imagesavealpha($source, true);
                imagewebp($source, $targetPath, 82);
            }

            imagedestroy($source);
        } else {
            // Fallback aman: jika GD gagal membaca, simpan file langsung
            // Hardcode extension berdasarkan MIME type — jangan percaya client input
            $ext = match ($mime) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                default => 'jpg',
            };
            $filename = $slugPrefix . time() . '-' . Str::random(5) . '.' . $ext;
            $file->move($dir, $filename);
        }

        return '/storage/' . $subDirectory . '/' . $filename;
    }
}
