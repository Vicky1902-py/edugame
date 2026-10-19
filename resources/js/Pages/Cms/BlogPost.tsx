import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Calendar, Eye, Share2, Tag, User as UserIcon } from 'lucide-react';

interface Post {
    id: number;
    title: string;
    slug: string;
    summary: string;
    content_html: string;
    featured_image?: string;
    views_count: number;
    published_at: string;
    meta_title?: string;
    meta_description?: string;
    category?: {
        name: string;
        slug: string;
    };
    author?: {
        name: string;
    };
}

interface Props {
    post: Post;
    relatedPosts: Post[];
}

export default function BlogPost({ post, relatedPosts }: Props) {
    return (
        <div className="min-h-screen bg-slate-950 text-slate-100 flex flex-col font-sans selection:bg-amber-500 selection:text-black">
            <Head>
                <title>{post.meta_title || post.title}</title>
                {post.meta_description && <meta name="description" content={post.meta_description} />}
            </Head>

            <header className="border-b border-slate-800 bg-slate-900/80 backdrop-blur sticky top-0 z-50">
                <div className="max-w-4xl mx-auto px-6 h-16 flex items-center justify-between">
                    <Link
                        href="/berita"
                        className="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-amber-400 transition"
                    >
                        <ArrowLeft className="w-4 h-4" /> Kembali ke Daftar Berita
                    </Link>
                    {post.category && (
                        <span className="text-xs font-semibold text-amber-400 bg-amber-500/10 px-2.5 py-1 rounded-full border border-amber-500/20">
                            {post.category.name}
                        </span>
                    )}
                </div>
            </header>

            <main className="flex-1 max-w-4xl w-full mx-auto px-6 py-12">
                <article className="bg-slate-900 border border-slate-800 rounded-2xl p-8 md:p-12 shadow-xl shadow-black/40">
                    <header className="mb-8">
                        <h1 className="text-3xl md:text-4xl font-extrabold text-white tracking-tight mb-4 leading-tight">
                            {post.title}
                        </h1>

                        <div className="flex flex-wrap items-center gap-4 text-xs text-slate-400 pb-6 border-b border-slate-800">
                            <span className="flex items-center gap-1.5">
                                <UserIcon className="w-3.5 h-3.5 text-amber-400" />
                                {post.author?.name || 'Redaksi EduGame'}
                            </span>
                            <span className="flex items-center gap-1.5">
                                <Calendar className="w-3.5 h-3.5 text-blue-400" />
                                {new Date(post.published_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}
                            </span>
                            <span className="flex items-center gap-1.5">
                                <Eye className="w-3.5 h-3.5 text-emerald-400" />
                                Dibaca {post.views_count} kali
                            </span>
                        </div>
                    </header>

                    {post.featured_image && (
                        <div className="mb-8 rounded-xl overflow-hidden max-h-96 w-full">
                            <img src={post.featured_image} alt={post.title} className="w-full h-full object-cover" />
                        </div>
                    )}

                    <div
                        className="prose prose-invert prose-amber max-w-none prose-headings:text-slate-100 prose-p:text-slate-300 prose-p:leading-relaxed prose-a:text-amber-400 prose-strong:text-white"
                        dangerouslySetInnerHTML={{ __html: post.content_html }}
                    />
                </article>

                {/* Related Posts */}
                {relatedPosts.length > 0 && (
                    <div className="mt-12">
                        <h3 className="text-xl font-bold text-white mb-6">Artikel Terkait Lainnya</h3>
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                            {relatedPosts.map((related) => (
                                <Link
                                    key={related.id}
                                    href={`/berita/${related.slug}`}
                                    className="p-4 bg-slate-900 border border-slate-800 rounded-xl hover:border-slate-700 transition flex flex-col group"
                                >
                                    <h4 className="text-sm font-bold text-white group-hover:text-amber-400 transition mb-2 line-clamp-2">
                                        {related.title}
                                    </h4>
                                    <span className="text-xs text-slate-500 mt-auto">
                                        {new Date(related.published_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })}
                                    </span>
                                </Link>
                            ))}
                        </div>
                    </div>
                )}
            </main>
        </div>
    );
}
