<?php

namespace Tests\Feature;

use App\Models\School;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolSeoEngineTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::create([
            'name'             => 'Rato Bangala Academy',
            'slug'             => 'rato-bangala',
            'email'            => 'info@ratobangala.edu.np',
            'city'             => 'Patan',
            'district'         => 'Lalitpur',
            'address'          => 'Patan Dhoka, Lalitpur',
            'status'           => 'active',
            'established_year' => '1992',
        ]);

        config(['tenant.school_id' => $this->school->id]);
    }

    /**
     * Test global sitemap.xml generates all school pages and articles.
     */
    public function test_sitemap_xml_contains_all_school_pages()
    {
        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/xml; charset=UTF-8');

        $content = $response->getContent();
        $this->assertStringContainsString('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', $content);
        $this->assertStringContainsString('/school/' . $this->school->slug, $content);
        $this->assertStringContainsString('/school/' . $this->school->slug . '/about', $content);
        $this->assertStringContainsString('/school/' . $this->school->slug . '/academics', $content);
        $this->assertStringContainsString('/school/' . $this->school->slug . '/admissions', $content);
        $this->assertStringContainsString('/school/' . $this->school->slug . '/blogs', $content);
        $this->assertStringContainsString('/school/' . $this->school->slug . '/news', $content);
        $this->assertStringContainsString('/school/' . $this->school->slug . '/stories', $content);
        $this->assertStringContainsString('/school/' . $this->school->slug . '/publications', $content);
        $this->assertStringContainsString('/school/' . $this->school->slug . '/notices', $content);
        $this->assertStringContainsString('/school/' . $this->school->slug . '/article/pedagogical-inquiry-modern-classroom', $content);
    }

    /**
     * Test dedicated per-school sitemap endpoint.
     */
    public function test_per_school_sitemap_xml()
    {
        $response = $this->get('/school/' . $this->school->slug . '/sitemap.xml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/xml; charset=UTF-8');

        $content = $response->getContent();
        $this->assertStringContainsString('/school/' . $this->school->slug . '/blogs', $content);
        $this->assertStringContainsString('/school/' . $this->school->slug . '/academics', $content);
    }

    /**
     * Test robots.txt allows public assets and points to sitemap.
     */
    public function test_robots_txt()
    {
        $response = $this->get('/robots.txt');

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('Allow: /storage/', $content);
        $this->assertStringContainsString('Allow: /school/', $content);
        $this->assertStringContainsString('Sitemap:', $content);
    }

    /**
     * Test OpenGraph and Schema.org metadata on school home.
     */
    public function test_school_homepage_seo_meta_and_json_ld()
    {
        $response = $this->get('/school/' . $this->school->slug);

        $response->assertStatus(200);

        // Meta tags
        $response->assertSee('<meta name="robots" content="index, follow', false);
        $response->assertSee('<meta property="og:site_name"', false);
        $response->assertSee('<meta property="og:type" content="website"', false);
        $response->assertSee('<meta name="twitter:card" content="summary_large_image"', false);

        // Schema.org JSON-LD
        $response->assertSee('"@type": "School"', false);
        $response->assertSee('"@type": "BreadcrumbList"', false);
        $response->assertSee('Rato Bangala Academy', false);
    }

    /**
     * Test rich article detail SEO with BlogPosting/NewsArticle schema.
     */
    public function test_article_detail_seo_meta_and_json_ld()
    {
        $response = $this->get('/school/' . $this->school->slug . '/article/pedagogical-inquiry-modern-classroom');

        $response->assertStatus(200);

        // Article OpenGraph
        $response->assertSee('<meta property="og:type" content="article"', false);
        $response->assertSee('property="article:published_time"', false);
        $response->assertSee('property="article:author"', false);

        // Schema.org JSON-LD for BlogPosting
        $response->assertSee('"@type": "BlogPosting"', false);
        $response->assertSee('"headline":', false);
        $response->assertSee('"author":', false);
        $response->assertSee('"publisher":', false);
    }
}

