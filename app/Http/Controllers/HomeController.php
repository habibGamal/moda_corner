<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Brand;
use App\Models\Category;
use App\Models\HeroSlide;
use App\Models\Section;
use App\Services\SectionService;
use Inertia\Inertia;

class HomeController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(SectionService $sectionService)
    {
        // Get all sections with their paginated products from the SectionService
        $sectionsData = $sectionService->getHomeSections();

        return Inertia::render('Home', [
            // Home page specific data
            'sections' => $sectionsData['sections'],

            // Add section products data
            ...$sectionsData['sectionsData'],

            // Add announcements
            'announcements' => $this->getAnnouncements(),

            // Add hero slides
            'heroSlides' => $this->getHeroSlides(),

            'categories' => Category::where('is_active', true)
                ->select('id', 'name_en', 'name_ar', 'slug', 'image', 'is_active')
                ->orderBy('display_order', 'asc')
                ->limit(10)
                ->get(),

            'brands' => Brand::where('is_active', true)
                ->select('id', 'name_en', 'name_ar', 'slug', 'image', 'is_active')
                ->limit(12)
                ->get(),
        ]);
    }

    /**
     * Get announcements for the top banner.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getAnnouncements()
    {
        return Announcement::where('is_active', true)
            ->orderBy('display_order', 'asc')
            ->get();
    }

    /**
     * Get hero slides for the carousel.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getHeroSlides()
    {
        return HeroSlide::where('is_active', true)
            ->orderBy('display_order', 'asc')
            ->get();
    }
}
