<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SchoolArticleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected School $school;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::create([
            'name'   => 'Rato Bangala Academy',
            'slug'   => 'rato-bangala',
            'email'  => 'info@ratobangala.edu.np',
            'status' => 'active',
        ]);

        config(['tenant.school_id' => $this->school->id]);

        $role = Role::firstOrCreate(['name' => 'admin']);
        $this->admin = User::factory()->create([
            'school_id' => $this->school->id,
            'role'      => 'admin',
        ]);
        $this->admin->assignRole('admin');
    }

    /**
     * Test admin can access articles index page.
     */
    public function test_admin_can_access_articles_index()
    {
        $response = $this->actingAs($this->admin)->get(route('school.articles.index'));
        $response->assertStatus(200);
        $response->assertSee('School Blogs, News &amp; Student Stories', false);
        $response->assertSee('Write New Blog / Story', false);
    }

    /**
     * Test admin can access article creation form.
     */
    public function test_admin_can_access_create_form()
    {
        $response = $this->actingAs($this->admin)->get(route('school.articles.create'));
        $response->assertStatus(200);
        $response->assertSee('Write &amp; Publish New Article', false);
        $response->assertSee('Publication Type &amp; Classification', false);
    }

    /**
     * Test admin can store and publish a new blog post.
     */
    public function test_admin_can_store_and_publish_blog()
    {
        Storage::fake('public');

        $image = UploadedFile::fake()->image('stem_symposium.jpg', 800, 600);

        $payload = [
            'type'           => 'blog',
            'category'       => 'Science & Technology',
            'title'          => 'Annual STEM & Robotics Exhibition 2026',
            'excerpt'        => 'Students presented automated drones, smart irrigation models, and AI research prototypes.',
            'content'        => '<p>Our annual STEM symposium welcomed over 500 parents and scientists to inspect student innovations.</p>',
            'author_name'    => 'Dr. Milan Dixit',
            'author_role'    => 'Principal',
            'read_time'      => '4 min read',
            'published_date' => 'Nov 09, 2026',
            'tags'           => 'STEM, Robotics, Innovation',
            'featured'       => 1,
            'image'          => $image,
        ];

        $response = $this->actingAs($this->admin)->post(route('school.articles.store'), $payload);

        $response->assertRedirect(route('school.articles.index'));
        $response->assertSessionHas('success');

        // Check stored in school settings
        $settings = $this->school->fresh()->settings;
        $this->assertNotEmpty($settings['custom_articles']);
        $firstArticle = $settings['custom_articles'][0];

        $this->assertEquals('Annual STEM & Robotics Exhibition 2026', $firstArticle['title']);
        $this->assertEquals('annual-stem-robotics-exhibition-2026', $firstArticle['slug']);
        $this->assertEquals('blog', $firstArticle['type']);
        $this->assertEquals('Science & Technology', $firstArticle['category']);
        $this->assertTrue($firstArticle['featured']);

        // Check live on website blogs page
        $webResponse = $this->get('/school/' . $this->school->slug . '/blogs');
        $webResponse->assertStatus(200);
        $webResponse->assertSee('Annual STEM & Robotics Exhibition 2026');

        // Check article detail reader page
        $readerResponse = $this->get('/school/' . $this->school->slug . '/article/' . $firstArticle['slug']);
        $readerResponse->assertStatus(200);
        $readerResponse->assertSee('Annual STEM & Robotics Exhibition 2026');
        $readerResponse->assertSee('annual STEM symposium welcomed over 500 parents', false);
    }

    /**
     * Test admin can edit and update an article.
     */
    public function test_admin_can_update_article()
    {
        // First store article
        $this->school->update([
            'settings' => [
                'custom_articles' => [
                    [
                        'slug'           => 'nepali-heritage-literature-week',
                        'type'           => 'story',
                        'type_label'     => 'Student Voice',
                        'category'       => 'Literature',
                        'title'          => 'Nepali Heritage Literature Week',
                        'excerpt'        => 'Original poetry and folklore storytelling.',
                        'author_name'    => 'Aarav Sharma',
                        'author_role'    => 'Grade 10 Scholar',
                        'author_avatar'  => null,
                        'published_date' => 'Nov 05, 2026',
                        'read_time'      => '3 min read',
                        'featured'       => false,
                        'image'          => 'imgs/hero/hero_1.webp',
                        'tags'           => ['Literature', 'Poetry'],
                        'content'        => '<p>Student poets recited original verses in Nepali and English.</p>',
                    ]
                ]
            ]
        ]);

        $editResponse = $this->actingAs($this->admin)->get(route('school.articles.edit', 'nepali-heritage-literature-week'));
        $editResponse->assertStatus(200);
        $editResponse->assertSee('Nepali Heritage Literature Week');

        // Update article
        $updatePayload = [
            'type'           => 'story',
            'category'       => 'Creative Writing',
            'title'          => 'Nepali Heritage Literature & Poetry Fest 2026',
            'excerpt'        => 'Updated excerpt for student poetry festival.',
            'content'        => '<p>Updated content with 50 original student poems.</p>',
            'author_name'    => 'Aarav Sharma & Editorial Council',
            'author_role'    => 'Student Editorial Council',
            'read_time'      => '5 min read',
            'published_date' => 'Nov 09, 2026',
            'tags'           => 'Poetry, Creative Writing, Heritage',
            'featured'       => 1,
        ];

        $putResponse = $this->actingAs($this->admin)->put(route('school.articles.update', 'nepali-heritage-literature-week'), $updatePayload);
        $putResponse->assertRedirect(route('school.articles.index'));

        $updatedArticle = $this->school->fresh()->getLandingArticleBySlug('nepali-heritage-literature-week');
        $this->assertEquals('Nepali Heritage Literature & Poetry Fest 2026', $updatedArticle['title']);
        $this->assertEquals('Creative Writing', $updatedArticle['category']);
    }

    /**
     * Test admin can delete an article.
     */
    public function test_admin_can_delete_article()
    {
        $this->school->update([
            'settings' => [
                'custom_articles' => [
                    [
                        'slug'  => 'temporary-article-to-delete',
                        'type'  => 'news',
                        'title' => 'Temporary Article',
                    ]
                ]
            ]
        ]);

        $deleteResponse = $this->actingAs($this->admin)->delete(route('school.articles.destroy', 'temporary-article-to-delete'));
        $deleteResponse->assertRedirect(route('school.articles.index'));

        $this->assertEmpty($this->school->fresh()->settings['custom_articles']);
    }
}
