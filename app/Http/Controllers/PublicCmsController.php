<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Banner;
use App\Models\ContactMessage;
use App\Models\Menu;
use App\Models\Page;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Question;
use App\Models\School;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicCmsController extends Controller
{
    public function home(): Response
    {
        $banners = Banner::where('is_active', true)->orderBy('sort_order')->get();
        $announcements = Announcement::where('is_active', true)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()))
            ->latest()
            ->take(3)
            ->get();
        $testimonials = Testimonial::where('is_featured', true)->orderBy('sort_order')->take(6)->get();
        $latestPosts = Post::where('is_published', true)->with('category')->latest('published_at')->take(3)->get();

        $headerMenu = Menu::where('location', 'header')->with('items')->first();
        $footerMenu1 = Menu::where('location', 'footer_1')->with('items')->first();
        $footerMenu2 = Menu::where('location', 'footer_2')->with('items')->first();

        return Inertia::render('Welcome', [
            'appName' => Setting::get('app_name', 'EduGame Indonesia'),
            'appTagline' => Setting::get('app_tagline', 'Belajar Interaktif & Menyenangkan Berbasis Gamifikasi'),
            'banners' => $banners,
            'announcements' => $announcements,
            'testimonials' => $testimonials,
            'latestPosts' => $latestPosts,
            'menus' => [
                'header' => $headerMenu?->items ?? [],
                'footer_1' => $footerMenu1?->items ?? [],
                'footer_2' => $footerMenu2?->items ?? [],
            ],
            'stats' => [
                'schools' => School::count(),
                'teachers' => User::where('role', 'guru')->count(),
                'students' => User::where('role', 'siswa')->count(),
                'questions' => Question::count(),
            ],
        ]);
    }

    public function showPage(string $slug): Response
    {
        $page = Page::where('slug', $slug)->where('is_published', true)->firstOrFail();

        return Inertia::render('Cms/StaticPage', [
            'page' => $page,
        ]);
    }

    public function blogIndex(Request $request): Response
    {
        $query = Post::where('is_published', true)->with('category', 'author');

        if ($request->filled('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->category));
        }

        if ($request->filled('q')) {
            $query->where('title', 'like', "%{$request->q}%");
        }

        $posts = $query->latest('published_at')->paginate(9)->withQueryString();
        $categories = PostCategory::withCount('posts')->get();

        return Inertia::render('Cms/BlogIndex', [
            'posts' => $posts,
            'categories' => $categories,
            'currentCategory' => $request->category,
            'searchQuery' => $request->q,
        ]);
    }

    public function blogShow(string $slug): Response
    {
        $post = Post::where('slug', $slug)->where('is_published', true)->with('category', 'author')->firstOrFail();
        $post->increment('views_count');

        $relatedPosts = Post::where('is_published', true)
            ->where('id', '!=', $post->id)
            ->where('category_id', $post->category_id)
            ->take(3)
            ->get();

        return Inertia::render('Cms/BlogPost', [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
        ]);
    }

    public function submitContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sender_name' => 'required|string|max:255',
            'sender_email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        ContactMessage::create($validated);

        return back()->with('success', 'Pesan Anda berhasil dikirim! Tim kami akan segera menghubungi Anda.');
    }
}
