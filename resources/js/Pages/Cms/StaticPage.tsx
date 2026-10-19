import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Calendar, FileText } from 'lucide-react';

interface Props {
    page: {
        title: string;
        content_html: string;
        meta_title?: string;
        meta_description?: string;
        updated_at: string;
    };
}

export default function StaticPage({ page }: Props) {
    return (
        <div className="min-h-screen bg-slate-950 text-slate-100 flex flex-col font-sans selection:bg-amber-500 selection:text-black">
            <Head>
                <title>{page.meta_title || page.title}</title>
                {page.meta_description && <meta name="description" content={page.meta_description} />}
            </Head>

            {/* Header Navigation */}
            <header className="border-b border-slate-800 bg-slate-900/80 backdrop-blur sticky top-0 z-50">
                <div className="max-w-4xl mx-auto px-6 h-16 flex items-center justify-between">
                    <Link
                        href="/"
                        className="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-amber-400 transition"
                    >
                        <ArrowLeft className="w-4 h-4" /> Kembali ke Beranda
                    </Link>
                    <span className="text-xs font-semibold uppercase tracking-wider text-amber-500 bg-amber-500/10 px-2.5 py-1 rounded-full border border-amber-500/20">
                        Dokumen Resmi Platform
                    </span>
                </div>
            </header>

            {/* Main Content */}
            <main className="flex-1 max-w-4xl w-full mx-auto px-6 py-12">
                <article className="bg-slate-900 border border-slate-800 rounded-2xl p-8 md:p-12 shadow-xl shadow-black/40">
                    <header className="mb-8 pb-6 border-b border-slate-800">
                        <h1 className="text-3xl md:text-4xl font-extrabold text-white tracking-tight mb-3">
                            {page.title}
                        </h1>
                        <div className="flex items-center gap-4 text-xs text-slate-400">
                            <span className="flex items-center gap-1.5">
                                <FileText className="w-3.5 h-3.5 text-amber-400" />
                                Halaman Informasi EduGame
                            </span>
                            <span className="flex items-center gap-1.5">
                                <Calendar className="w-3.5 h-3.5 text-blue-400" />
                                Terakhir diperbarui: {new Date(page.updated_at).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' })}
                            </span>
                        </div>
                    </header>

                    <div
                        className="prose prose-invert prose-amber max-w-none prose-headings:text-slate-100 prose-p:text-slate-300 prose-p:leading-relaxed prose-a:text-amber-400 prose-strong:text-white"
                        dangerouslySetInnerHTML={{ __html: page.content_html }}
                    />
                </article>
            </main>

            {/* Footer */}
            <footer className="border-t border-slate-800 py-6 text-center text-xs text-slate-500">
                &copy; {new Date().getFullYear()} EduGame Platform. Seluruh Hak Cipta Dilindungi Undang-Undang.
            </footer>
        </div>
    );
}
