<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\SectionType;
use App\Models\Product;
use App\Models\Section;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SectionService
{
    /**
     * The feed scroll service for handling product pagination
     */
    protected FeedScrollService $feedScrollService;

    /**
     * The recommendation section service for personalized product recommendations
     */
    protected RecommendationSectionService $recommendationSectionService;

    /**
     * Create a new section service instance.
     */
    public function __construct(
        FeedScrollService $feedScrollService,
        RecommendationSectionService $recommendationSectionService
    ) {
        $this->feedScrollService = $feedScrollService;
        $this->recommendationSectionService = $recommendationSectionService;
    }

    /**
     * Get all sections with their products for the home page.
     *
     * @param  int  $perPage  Number of products per page
     * @return array{sections: Collection, sectionsData: Collection} Data structure with sections and their paginated products
     */
    public function getHomeSections(int $perPage = 10): array
    {
        // Get sections
        $realSections = $this->getRealSections();
        $virtualSections = $this->getVirtualSections();
        $allSections = $realSections->merge($virtualSections)->sortBy(
            fn ($section) => $section->sort_order
        )->values();

        // Load products for each active section with pagination
        $sectionsPaginators = $this->loadSectionProducts($realSections, $virtualSections, $perPage);

        return [
            'sections' => $allSections,
            'sectionsData' => $sectionsPaginators,
        ];
    }

    /**
     * Load products for all sections with pagination
     */
    private function loadSectionProducts(Collection $realSections, Collection $virtualSections, int $perPage): Collection
    {
        $sectionsPaginators = collect();

        // Process real sections
        foreach ($realSections as $section) {
            $sectionProductsData = $this->getProductsInRealSection($section->id, paginate: true, perPage: $perPage);
            $sectionsPaginators->push($sectionProductsData);
        }

        // Process virtual sections
        foreach ($virtualSections as $section) {
            $sectionProductsData = $this->getProductsInVirtualSection(
                $section->id,
                $section->title_en,
                paginate: true,
                perPage: $perPage
            );
            $sectionsPaginators->push($sectionProductsData);
        }

        // Flatten the sections collection to a key-value map for easier access
        return $sectionsPaginators->flatMap(function ($section) {
            return $section;
        });
    }

    /**
     * Get all active real sections.
     *
     * @return Collection<int, Section>
     */
    public function getRealSections(): Collection
    {
        return $this->getSectionsByType(SectionType::REAL);
    }

    /**
     * Get all active virtual sections.
     *
     * @return Collection<int, Section>
     */
    public function getVirtualSections(): Collection
    {
        return $this->getSectionsByType(SectionType::VIRTUAL);
    }

    /**
     * Get sections by type
     *
     * @return Collection<int, Section>
     */
    private function getSectionsByType(SectionType $type): Collection
    {
        return Section::where('active', true)
            ->where('section_type', $type)
            ->orderBy('sort_order', 'asc')
            ->get();
    }

    /**
     * Get a specific section by ID and its products with pagination.
     *
     * @param  bool  $paginate  Whether to paginate the products
     * @param  int  $perPage  Number of products per page if paginated
     * @return array|Section|null Section with products or paginated feed data
     */
    public function getProductsInRealSection(int $id, bool $paginate = false, int $perPage = 10): array|Section|null
    {
        // First, get the section
        $section = $this->findActiveSection($id);

        if (! $section) {
            return null;
        }

        // If pagination is requested, use FeedScrollService
        if ($paginate) {
            // $query = $section->pro
            $query = Product::forCards()
                ->whereIn('products.id', function ($subquery) use ($section) {
                    $subquery->select('product_id')
                        ->from('section_product')
                        ->where('section_id', $section->id);
                });

            // dd($query->toSql(),$section->products()->getQuery()->toSql());
            return $this->paginateProducts($query, (string) $section->id, $perPage);
        }

        // Otherwise, use the traditional eager loading
        $section->load([
            'products' => function ($query) {
                $query->forCards();
            },
        ]);

        return $section;
    }

    /**
     * Find an active section by ID
     */
    private function findActiveSection(int $id): ?Section
    {
        return Section::where('id', $id)
            ->where('active', true)
            ->first();
    }

    /**
     * Paginate products query using FeedScrollService
     */
    private function paginateProducts(Builder $query, string $sectionId, int $perPage): array
    {
        return $this->feedScrollService->fromQuery($query)
            ->forSection($sectionId)
            ->whereConditions(['is_active' => true])
            ->orderBy('products.id', 'desc')
            ->perPage($perPage)
            ->get();
    }

    /**
     * Get products for virtual sections with pagination.
     *
     * @param  int  $id  Section ID
     * @param  string  $section_name  The virtual section type to fetch
     * @param  bool  $paginate  Whether to paginate the products
     * @param  int  $perPage  Number of products per page if paginated
     * @return array Paginated feed data
     */
    public function getProductsInVirtualSection(int $id, string $section_name, bool $paginate = false, int $perPage = 10): array
    {
        // Choose the appropriate query based on section name
        $query = $this->getVirtualSectionQuery($section_name);

        // If pagination is requested, use FeedScrollService
        if ($paginate) {
            return $this->paginateProducts($query, (string) $id, $perPage);
        }

        // Otherwise, just return the products
        return ['data' => $query->get()];
    }

    /**
     * Get the appropriate query based on virtual section name
     */
    private function getVirtualSectionQuery(string $section_name): Builder
    {
        return match ($section_name) {
            'Featured Products' => $this->getFeaturedProductsSectionQuery(),
            'Products On Sale' => $this->getOnSaleProductsSectionQuery(),
            'New Arrivals' => $this->getNewProductsSectionQuery(),
            'Best Sellers' => $this->getBestSellersProductsSectionQuery(),
            'Recommended For You' => $this->getRecommendedProductsSectionQuery(),
            default => Product::forCards(),
        };
    }

    /**
     * Get query for featured products
     */
    protected function getFeaturedProductsSectionQuery(): Builder
    {
        return Product::forCards()
            ->where('is_featured', true);
    }

    /**
     * Get query for new arrivals products (created in the last 7 days)
     */
    protected function getNewProductsSectionQuery(): Builder
    {
        return Product::forCards()
            ->where('created_at', '>=', now()->subDays(7));
    }

    /**
     * Get query for products on sale
     */
    protected function getOnSaleProductsSectionQuery(): Builder
    {
        return Product::forCards()
            ->whereNotNull('sale_price');
    }

    /**
     * Get query for best selling products (ordered by total sales quantity)
     */
    protected function getBestSellersProductsSectionQuery(): Builder
    {
        // Create a subquery to get product sales totals
        $salesSubquery = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.order_status', OrderStatus::DELIVERED->value)
            ->select('order_items.product_id', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->groupBy('order_items.product_id');

        // Join with the subquery and order by sales
        return Product::forCards()
            ->leftJoinSub($salesSubquery, 'sales', function ($join) {
                $join->on('products.id', '=', 'sales.product_id');
            })
            ->select('products.*', DB::raw('COALESCE(sales.total_sold, 0) as total_sold'))
            ->orderBy('total_sold', 'desc');
    }

    /**
     * Get query for recommended products
     *
     * Delegates to the RecommendationSectionService which handles:
     * - Products from categories of user's past orders
     * - Products from brands of user's past orders
     * - Products related to items in the user's wishlist
     * - Products related to items in the user's cart
     * - Featured products if no user history available
     * - Recently added products as a fallback
     */
    protected function getRecommendedProductsSectionQuery(): Builder
    {
        return $this->recommendationSectionService->getRecommendedProductsQuery();
    }
}
