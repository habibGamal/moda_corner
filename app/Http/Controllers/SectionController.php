<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Services\SectionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SectionController extends Controller
{
    /**
     * The section service instance.
     */
    protected SectionService $sectionService;

    /**
     * Create a new controller instance.
     */
    public function __construct(SectionService $sectionService)
    {
        $this->sectionService = $sectionService;
    }

    /**
     * Display the products in a specific section.
     *
     * @return \Inertia\Response
     */
    public function show(Section $section, Request $request)
    {
        $perPage = $request->input('per_page', 12);

        // Different handling based on section type
        if ($section->section_type->value === 'REAL') {
            $sectionPaginator = $this->sectionService->getProductsInRealSection($section->id, paginate: true, perPage: $perPage);
        } else {
            $sectionPaginator = $this->sectionService->getProductsInVirtualSection(
                $section->id,
                $section->title_en,
                paginate: true,
                perPage: $perPage
            );
        }

        return Inertia::render('Products/Section', [
            'section' => $section,
            ...$sectionPaginator,
        ]);
    }
}
