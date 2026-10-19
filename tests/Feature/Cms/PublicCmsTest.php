<?php

namespace Tests\Feature\Cms;

use App\Models\ContactMessage;
use App\Models\Page;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicCmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_homepage_renders_successfully_with_cms_data(): void
    {
        $response = $this->get('/');
        $response->assertSuccessful();
    }

    public function test_published_static_page_can_be_viewed(): void
    {
        $page = Page::create([
            'slug' => 'tentang-kami',
            'title' => 'Tentang Platform EduGame',
            'content_html' => '<p>Platform game edukasi terdepan di Indonesia.</p>',
            'is_published' => true,
        ]);

        $response = $this->get('/halaman/tentang-kami');
        $response->assertSuccessful();
    }

    public function test_unpublished_static_page_returns_404(): void
    {
        Page::create([
            'slug' => 'rahasia-internal',
            'title' => 'Halaman Rahasia',
            'content_html' => '<p>Draft</p>',
            'is_published' => false,
        ]);

        $this->get('/halaman/rahasia-internal')->assertNotFound();
    }

    public function test_blog_index_page_can_be_rendered(): void
    {
        $category = PostCategory::create([
            'name' => 'Kabar Sekolah',
            'slug' => 'kabar-sekolah',
        ]);

        $author = User::factory()->create();

        Post::create([
            'category_id' => $category->id,
            'author_id' => $author->id,
            'title' => 'Pembaruan Fitur Baru EduGame',
            'slug' => 'pembaruan-fitur-baru-edugame',
            'summary' => 'Ringkasan fitur.',
            'content_html' => '<p>Detail artikel.</p>',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $response = $this->get('/berita');
        $response->assertSuccessful();
    }

    public function test_single_blog_post_can_be_viewed_and_increments_views(): void
    {
        $category = PostCategory::create([
            'name' => 'Tips Belajar',
            'slug' => 'tips-belajar',
        ]);

        $author = User::factory()->create();

        $post = Post::create([
            'category_id' => $category->id,
            'author_id' => $author->id,
            'title' => 'Cara Menyenangkan Belajar Matematika',
            'slug' => 'cara-menyenangkan-belajar-matematika',
            'summary' => 'Tips ampuh.',
            'content_html' => '<p>Langkah-langkah...</p>',
            'is_published' => true,
            'published_at' => now(),
            'views_count' => 0,
        ]);

        $response = $this->get('/berita/cara-menyenangkan-belajar-matematika');
        $response->assertSuccessful();

        $post->refresh();
        $this->assertEquals(1, $post->views_count);
    }

    public function test_public_contact_form_submission_stores_message(): void
    {
        $response = $this->post('/kontak', [
            'sender_name' => 'Budi Santoso',
            'sender_email' => 'budi@sekolah.sch.id',
            'phone' => '081234567890',
            'subject' => 'Pertanyaan Kemitraan Sekolah',
            'message' => 'Halo, kami dari SDN 1 ingin mengajukan kemitraan lisensi EduGame.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('contact_messages', [
            'sender_name' => 'Budi Santoso',
            'sender_email' => 'budi@sekolah.sch.id',
            'subject' => 'Pertanyaan Kemitraan Sekolah',
            'is_read' => false,
        ]);
    }
}
