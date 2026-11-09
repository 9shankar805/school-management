<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Services\ImageWebpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SchoolArticleController extends Controller
{
    /**
     * Resolve the active school for the authenticated user/context.
     */
    private function resolveSchool(): School
    {
        $user = auth()->user();
        $school = $user->school;

        if (!$school && $user->school_id) {
            $school = School::find($user->school_id);
        }
        if (!$school && config('tenant.school_id')) {
            $school = School::find(config('tenant.school_id'));
        }
        if (!$school && session()->has('tenant_school_id')) {
            $school = School::find(session('tenant_school_id'));
        }
        if (!$school && in_array($user->role, ['super_admin', 'admin'])) {
            $school = School::first();
        }

        if (!$school) {
            abort(404, 'Active school not found.');
        }

        return $school;
    }

    /**
     * Display a listing of all articles (blogs, news, stories, publications).
     */
    public function index(Request $request): View
    {
        $school = $this->resolveSchool();
        $filterType = $request->query('type');

        // All articles from model (both custom and defaults)
        $allArticles = $school->getLandingArticles($filterType);

        // Separate custom articles created by school admin
        $settings = $school->settings ?? [];
        $customArticles = $settings['custom_articles'] ?? [];

        if ($filterType) {
            $customArticles = array_values(array_filter($customArticles, function ($item) use ($filterType) {
                return ($item['type'] ?? '') === $filterType;
            }));
        }

        $stats = [
            'total'        => count($school->getLandingArticles()),
            'blogs'        => count($school->getLandingArticles('blog')),
            'news'         => count($school->getLandingArticles('news')),
            'stories'      => count($school->getLandingArticles('story')),
            'publications' => count($school->getLandingArticles('publication')),
            'custom_count' => count($settings['custom_articles'] ?? []),
        ];

        return view('school-articles.index', compact('school', 'allArticles', 'customArticles', 'stats', 'filterType'));
    }

    /**
     * Show the form for creating a new article / blog.
     */
    public function create(Request $request): View
    {
        $school = $this->resolveSchool();
        $defaultType = $request->query('type', 'blog');

        return view('school-articles.create', compact('school', 'defaultType'));
    }

    /**
     * Store a newly created article / blog.
     */
    public function store(Request $request): RedirectResponse
    {
        $school = $this->resolveSchool();

        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'type'           => 'required|in:blog,news,story,publication',
            'category'       => 'nullable|string|max:100',
            'excerpt'        => 'required|string|max:600',
            'content'        => 'required|string',
            'author_name'    => 'nullable|string|max:150',
            'author_role'    => 'nullable|string|max:150',
            'read_time'      => 'nullable|string|max:50',
            'published_date' => 'nullable|string|max:100',
            'tags'           => 'nullable|string|max:255',
            'featured'       => 'nullable|boolean',
            'image'          => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
        ]);

        $typeLabels = [
            'blog'        => 'School Blog',
            'news'        => 'News & Press',
            'story'       => 'Student Voice',
            'publication' => 'Campus Publication',
        ];

        $fallbackImages = [
            'blog'        => 'imgs/hero/hero_1.webp',
            'news'        => 'imgs/hero/hero_2.webp',
            'story'       => 'imgs/hero/hero_3.webp',
            'publication' => 'imgs/hero/hero_4.webp',
        ];

        $title = trim($validated['title']);
        $slug = Str::slug($title);
        if (empty($slug)) {
            $slug = 'article-' . time();
        }

        $settings = $school->settings ?? [];
        $customArticles = $settings['custom_articles'] ?? [];

        // Check if slug exists in custom articles
        foreach ($customArticles as $art) {
            if (($art['slug'] ?? '') === $slug) {
                $slug .= '-' . Str::random(5);
                break;
            }
        }

        $imagePath = null;
        $imageUrl = null;

        if ($request->hasFile('image')) {
            $imagePath = ImageWebpService::storeAsWebp($request->file('image'), 'school-articles', 'public');
            $imageUrl = asset('storage/' . $imagePath);
        } else {
            $imageUrl = asset($fallbackImages[$validated['type']] ?? 'imgs/hero/hero_1.webp');
        }

        $tagsArray = [];
        if (!empty($validated['tags'])) {
            $tagsArray = array_values(array_filter(array_map('trim', explode(',', $validated['tags']))));
        }
        if (empty($tagsArray)) {
            $tagsArray = [$typeLabels[$validated['type']], $validated['category'] ?? 'Campus Life'];
        }

        $user = auth()->user();
        $authorName = !empty($validated['author_name']) ? trim($validated['author_name']) : ($user ? $user->name : $school->principal_name_resolved);
        $authorRole = !empty($validated['author_role']) ? trim($validated['author_role']) : 'Faculty & Contributor';

        $article = [
            'slug'           => $slug,
            'type'           => $validated['type'],
            'type_label'     => $typeLabels[$validated['type']],
            'category'       => !empty($validated['category']) ? trim($validated['category']) : 'General',
            'title'          => $title,
            'excerpt'        => trim($validated['excerpt']),
            'author_name'    => $authorName,
            'author_role'    => $authorRole,
            'author_avatar'  => $user?->avatar ?: $school->principal_photo_url,
            'published_date' => !empty($validated['published_date']) ? trim($validated['published_date']) : now()->format('M d, Y'),
            'read_time'      => !empty($validated['read_time']) ? trim($validated['read_time']) : '4 min read',
            'featured'       => $request->boolean('featured'),
            'image'          => $imageUrl,
            'image_path'     => $imagePath,
            'tags'           => $tagsArray,
            'content'        => $validated['content'],
            'created_at'     => now()->toIso8601String(),
            'updated_at'     => now()->toIso8601String(),
        ];

        // Place newest custom article at beginning
        array_unshift($customArticles, $article);
        $settings['custom_articles'] = $customArticles;
        $school->settings = $settings;
        $school->save();

        return redirect()->route('school.articles.index')->with('success', "Article '{$title}' published successfully! It is now live on your school website.");
    }

    /**
     * Show the form for editing an article / blog.
     */
    public function edit(string $slug): View
    {
        $school = $this->resolveSchool();
        $article = $school->getLandingArticleBySlug($slug);

        if (!$article) {
            abort(404, 'Article not found.');
        }

        $settings = $school->settings ?? [];
        $customArticles = $settings['custom_articles'] ?? [];
        $isCustom = false;

        foreach ($customArticles as $custom) {
            if (($custom['slug'] ?? '') === $slug) {
                $isCustom = true;
                break;
            }
        }

        return view('school-articles.edit', compact('school', 'article', 'slug', 'isCustom'));
    }

    /**
     * Update an article / blog.
     */
    public function update(Request $request, string $slug): RedirectResponse
    {
        $school = $this->resolveSchool();

        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'type'           => 'required|in:blog,news,story,publication',
            'category'       => 'nullable|string|max:100',
            'excerpt'        => 'required|string|max:600',
            'content'        => 'required|string',
            'author_name'    => 'nullable|string|max:150',
            'author_role'    => 'nullable|string|max:150',
            'read_time'      => 'nullable|string|max:50',
            'published_date' => 'nullable|string|max:100',
            'tags'           => 'nullable|string|max:255',
            'featured'       => 'nullable|boolean',
            'image'          => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
        ]);

        $typeLabels = [
            'blog'        => 'School Blog',
            'news'        => 'News & Press',
            'story'       => 'Student Voice',
            'publication' => 'Campus Publication',
        ];

        $settings = $school->settings ?? [];
        $customArticles = $settings['custom_articles'] ?? [];

        // Find existing article index
        $foundIndex = null;
        $existingArticle = null;
        foreach ($customArticles as $idx => $art) {
            if (($art['slug'] ?? '') === $slug) {
                $foundIndex = $idx;
                $existingArticle = $art;
                break;
            }
        }

        // If not in custom articles, locate in defaults and convert into custom
        if ($foundIndex === null) {
            $existingArticle = $school->getLandingArticleBySlug($slug);
        }

        $imagePath = $existingArticle['image_path'] ?? null;
        $imageUrl  = $existingArticle['image'] ?? asset('imgs/hero/hero_1.webp');

        if ($request->hasFile('image')) {
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = ImageWebpService::storeAsWebp($request->file('image'), 'school-articles', 'public');
            $imageUrl = asset('storage/' . $imagePath);
        }

        $tagsArray = [];
        if (!empty($validated['tags'])) {
            $tagsArray = array_values(array_filter(array_map('trim', explode(',', $validated['tags']))));
        } else {
            $tagsArray = $existingArticle['tags'] ?? [$typeLabels[$validated['type']]];
        }

        $updatedArticle = [
            'slug'           => $slug,
            'type'           => $validated['type'],
            'type_label'     => $typeLabels[$validated['type']],
            'category'       => !empty($validated['category']) ? trim($validated['category']) : ($existingArticle['category'] ?? 'General'),
            'title'          => trim($validated['title']),
            'excerpt'        => trim($validated['excerpt']),
            'author_name'    => !empty($validated['author_name']) ? trim($validated['author_name']) : ($existingArticle['author_name'] ?? 'Faculty'),
            'author_role'    => !empty($validated['author_role']) ? trim($validated['author_role']) : ($existingArticle['author_role'] ?? 'Contributor'),
            'author_avatar'  => $existingArticle['author_avatar'] ?? $school->principal_photo_url,
            'published_date' => !empty($validated['published_date']) ? trim($validated['published_date']) : ($existingArticle['published_date'] ?? now()->format('M d, Y')),
            'read_time'      => !empty($validated['read_time']) ? trim($validated['read_time']) : ($existingArticle['read_time'] ?? '4 min read'),
            'featured'       => $request->boolean('featured'),
            'image'          => $imageUrl,
            'image_path'     => $imagePath,
            'tags'           => $tagsArray,
            'content'        => $validated['content'],
            'created_at'     => $existingArticle['created_at'] ?? now()->toIso8601String(),
            'updated_at'     => now()->toIso8601String(),
        ];

        if ($foundIndex !== null) {
            $customArticles[$foundIndex] = $updatedArticle;
        } else {
            array_unshift($customArticles, $updatedArticle);
        }

        $settings['custom_articles'] = $customArticles;
        $school->settings = $settings;
        $school->save();

        return redirect()->route('school.articles.index')->with('success', "Article '{$updatedArticle['title']}' updated successfully!");
    }

    /**
     * Delete an article / blog.
     */
    public function destroy(string $slug): RedirectResponse
    {
        $school = $this->resolveSchool();
        $settings = $school->settings ?? [];
        $customArticles = $settings['custom_articles'] ?? [];

        $newArticles = [];
        $deletedTitle = 'Article';

        foreach ($customArticles as $art) {
            if (($art['slug'] ?? '') === $slug) {
                $deletedTitle = $art['title'] ?? 'Article';
                if (!empty($art['image_path']) && Storage::disk('public')->exists($art['image_path'])) {
                    Storage::disk('public')->delete($art['image_path']);
                }
            } else {
                $newArticles[] = $art;
            }
        }

        $settings['custom_articles'] = $newArticles;
        $school->settings = $settings;
        $school->save();

        return redirect()->route('school.articles.index')->with('success', "Article '{$deletedTitle}' deleted successfully.");
    }
}

