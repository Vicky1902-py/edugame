import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Calendar, Eye, Search, Tag } from 'lucide-react';

interface Post {
    id: number;
    title: string;
    slug: string;
    summary: string;
    featured_image?: string;
    views_count: number;
    published_at: string;
    category?: {
        name: string;
        slug: string;
    };
    author?: {
        name: string;
    };
}

interface Props {
    posts: {
        data: Post[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    categories: Array<{ id: number; name: string; slug: string; posts_count: number }>;
    currentCategory?: string;
    searchQuery?: string;
}

export default function BlogIndex({ posts, categories, currentCategory, searchQuery }: Props) {
    const handleSearch = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        const formData = new FormData(e.currentTarget);
        const q = formData.get('q') as string;
        router.get('/berita', { q, category: currentCategory }, { preserveState: true });
    };

    return (
        <div className="min-h-screen bg-slate-950 text-slate-100 flex flex-col font-sans selection:bg-amber-500 selection:text-black">
            <Head title="Berita & Artikel Edukasi - EduGame" />

            <header className="border-b border-slate-800 bg-slate-900/80 backdrop-blur sticky top-0 z-50">
                <div className="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
                    <Link
                        href="/"
                        className="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-amber-400 transition"
                    >
                        <ArrowLeft className="w-4 h-4" /> Kembali ke Beranda
                    </Link>
                    <span className="text-base font-bold text-white tracking-tight">
                        Warta & Blog <span className="text-amber-400">EduGame</span>
                    </span>
                </div>
            </header>

            <main className="flex-1 max-w-6xl w-full mx-auto px-6 py-12">
                <div className="text-center max-w-2xl mx-auto mb-10">
                    <h1 className="text-3xl md:text-4xl font-extrabold text-white tracking-tight mb-3">
                        Kabar Pendidikan & Gamifikasi
                    </h1>
                    <p className="text-sm md:text-base text-slate-400">
                        Inspirasi metode belajar interaktif, pembaruan fitur platform, dan kisah sukses sekolah di seluruh Indonesia.
                    </p>
                </div>

                {/* Filter & Search */}
                <div className="flex flex-col md:flex-row items-center justify-between gap-4 mb-8">
                    <div className="flex flex-wrap items-center gap-2">
                        <Link
                            href="/berita"
                            className={`px-3 py-1.5 rounded-full text-xs font-medium transition ${
                                !currentCategory
                                    ? 'bg-amber-500 text-slate-950 font-bold'
                                    : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800'
                            }`}
                        >
                            Semua Kategori
                        </Link>
                        {categories.map((cat) => (
                            <Link
                                key={cat.id}
                                href={`/berita?category=${cat.slug}`}
                                className={`px-3 py-1.5 rounded-full text-xs font-medium transition ${
                                    currentCategory === cat.slug
                                        ? 'bg-amber-500 text-slate-950 font-bold'
                                        : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800'
                                }`}
                            >
                                {cat.name} ({cat.posts_count})
                            </Link>
                        ))}
                    </div>

                    <form onSubmit={handleSearch} className="relative w-full md:w-64">
                        <input
                            type="text"
                            name="q"
                            defaultValue={searchQuery}
                            placeholder="Cari artikel..."
                            className="w-full bg-slate-900 border border-slate-800 rounded-lg pl-9 pr-3 py-1.5 text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:border-amber-500"
                        />
                        <Search className="w-3.5 h-3.5 text-slate-500 absolute left-3 top-2.5" />
                    </form>
                </div>

                {/* Posts Grid */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {posts.data.map((post) => (
                        <article
                            key={post.id}
                            className="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden hover:border-slate-700 transition flex flex-col group shadow-lg shadow-black/20"
                        >
                            <div className="h-44 bg-slate-800 relative overflow-hidden">
                                {post.featured_image ? (
                                    <img
                                        src={post.featured_image}
                                        alt={post.title}
                                        className="w-full h-full object-cover group-hover:scale-105 transition duration-500"
                                    />
                                ) : (
                                    <div className="w-full h-full flex items-center justify-center bg-gradient-to-br from-amber-500/10 via-slate-800 to-slate-900 text-amber-500">
                                        <Tag className="w-10 h-10 opacity-30" />
                                    </div>
                                )}
                                {post.category && (
                                    <span className="absolute top-3 left-3 bg-slate-950/80 backdrop-blur text-amber-400 text-xs px-2.5 py-1 rounded-md border border-amber-500/20 font-medium">
                                        {post.category.name}
                                    </span>
                                )}
                            </div>

                            <div className="p-6 flex-1 flex flex-col">
                                <div className="flex items-center gap-3 text-xs text-slate-400 mb-2">
                                    <span className="flex items-center gap-1">
                                        <Calendar className="w-3 h-3 text-slate-500" />
                                        {new Date(post.published_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })}
                                    </span>
                                    <span className="flex items-center gap-1">
                                        <Eye className="w-3 h-3 text-slate-500" />
                                        {post.views_count}
                                    </span>
                                </div>

                                <h2 className="text-lg font-bold text-white group-hover:text-amber-400 transition mb-2 line-clamp-2">
                                    <Link href={`/berita/${post.slug}`}>{post.title}</Link>
                                </h2>

                                <p className="text-xs text-slate-400 line-clamp-3 mb-4 flex-1">
                                    {post.summary}
                                </p>

                                <Link
                                    href={`/berita/${post.slug}`}
                                    className="text-xs font-semibold text-amber-400 hover:text-amber-300 inline-flex items-center gap-1 mt-auto"
                                >
                                    Baca Selengkapnya &rarr;
                                </Link>
                            </div>
                        </article>
                    ))}
                </div>

                {posts.data.length === 0 && (
                    <div className="text-center py-16 bg-slate-900 border border-slate-800 rounded-2xl text-slate-400">
                        Tidak ada artikel berita yang ditemukan.
                    </div>
                )}
            </main>
        </div>
    );
}
