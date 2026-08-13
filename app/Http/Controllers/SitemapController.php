<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Modules\BlogManagement\Service\Interfaces\BlogServiceInterface;
use Modules\BlogManagement\Service\Interfaces\BlogCategoryServiceInterface;

class SitemapController extends Controller
{
    protected $blogService;
    protected $blogCategoryService;

    public function __construct(BlogServiceInterface $blogService, BlogCategoryServiceInterface $blogCategoryService)
    {
        $this->blogService = $blogService;
        $this->blogCategoryService = $blogCategoryService;
    }

    public function index(): Response
    {
        $baseUrl = config('app.url', 'https://zerinexpress.com');

        $staticUrls = [
            ['url' => $baseUrl . '/', 'priority' => '1.0', 'changefreq' => 'daily'],
            ['url' => $baseUrl . '/about-us', 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['url' => $baseUrl . '/contact-us', 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['url' => $baseUrl . '/blog', 'priority' => '0.9', 'changefreq' => 'daily'],
            ['url' => $baseUrl . '/blog/customer-app-download', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['url' => $baseUrl . '/blog/driver-app-download', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['url' => $baseUrl . '/privacy', 'priority' => '0.5', 'changefreq' => 'yearly'],
            ['url' => $baseUrl . '/terms', 'priority' => '0.5', 'changefreq' => 'yearly'],
        ];

        $dynamicUrls = [];

        try {
            $blogs = $this->blogService->getBy(criteria: ['is_active' => 1], relations: ['category']);
            foreach ($blogs as $blog) {
                $dynamicUrls[] = [
                    'url' => $baseUrl . '/blog/details/' . $blog->slug,
                    'priority' => '0.7',
                    'changefreq' => 'weekly',
                    'lastmod' => $blog->updated_at ? $blog->updated_at->toDateString() : null,
                ];
            }
        } catch (\Exception $e) {
        }

        try {
            $categories = $this->blogCategoryService->getBy(criteria: ['is_active' => 1]);
            foreach ($categories as $category) {
                $dynamicUrls[] = [
                    'url' => $baseUrl . '/blog/' . $category->slug,
                    'priority' => '0.6',
                    'changefreq' => 'weekly',
                ];
            }
        } catch (\Exception $e) {
        }

        $allUrls = array_merge($staticUrls, $dynamicUrls);

        $content = view('sitemap.xml', ['urls' => $allUrls])->render();

        return response($content, 200, [
            'Content-Type' => 'application/xml',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
