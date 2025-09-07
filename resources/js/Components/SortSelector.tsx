import { useI18n } from "@/hooks/use-i18n";
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/Components/ui/select";
import { ArrowDownAZ, ArrowDownZA, ArrowDown01, ArrowDown10, Clock } from "lucide-react";
import { router } from "@inertiajs/react";

interface SortSelectorProps {
  sortBy: string;
  query: string;
  selectedBrands: string[];
  selectedCategories: string[];
  minPrice: number | null;
  maxPrice: number | null;
}

export function SortSelector({
  sortBy = "newest",
  query = "",
  selectedBrands = [],
  selectedCategories = [],
  minPrice = null,
  maxPrice = null,
}: SortSelectorProps) {
  const { t, currentLocale , direction } = useI18n();
  const isRTL = direction === 'rtl';

  const handleSortChange = (value: string) => {
    router.get("/search", {
      q: query,
      brands: selectedBrands,
      categories: selectedCategories,
      min_price: minPrice,
      max_price: maxPrice,
      sort_by: value,
    });
  };

  const sortOptions = [
    { value: "newest", label: t("newest", "Newest"), icon: Clock },
    { value: "price_low_high", label: t("price_low_high", "Price: Low to High"), icon: ArrowDown01 },
    { value: "price_high_low", label: t("price_high_low", "Price: High to Low"), icon: ArrowDown10 },
    { value: "name_a_z", label: t("name_a_z", "Name: A to Z"), icon: ArrowDownAZ },
    { value: "name_z_a", label: t("name_z_a", "Name: Z to A"), icon: ArrowDownZA },
  ];

  return (
    <div className="flex items-center gap-2" dir={direction}>
      <span className="text-sm text-muted-foreground hidden md:inline-block">
        {t("sort_by", "Sort by")}:
      </span>
      <Select value={sortBy} onValueChange={handleSortChange} dir={direction}>
        <SelectTrigger className="w-[150px] sm:w-[180px]">
          <SelectValue placeholder={t("sort_by", "Sort by")} />
        </SelectTrigger>
        <SelectContent>
          <SelectGroup>
            {sortOptions.map(option => {
              const Icon = option.icon;
              return (
                <SelectItem
                  key={option.value}
                  value={option.value}
                  className="flex items-center gap-2"
                >
                  <div className="flex items-center gap-2">
                    <Icon className="h-4 w-4" />
                    <span>{option.label}</span>
                  </div>
                </SelectItem>
              );
            })}
          </SelectGroup>
        </SelectContent>
      </Select>
    </div>
  );
}
