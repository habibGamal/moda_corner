import { useI18n } from "@/hooks/use-i18n";

interface CartHeaderProps {
  totalItems: number;
}

export function CartHeader({ totalItems }: CartHeaderProps) {
  const { t } = useI18n();

  return (
    <div>
      <h1 className="text-3xl font-bold tracking-tight">{t('shopping_cart', 'Shopping Cart')}</h1>
      <p className="text-muted-foreground mt-2">
        {t('cart_items_count', 'You have {{count}} items in your cart', { count: totalItems })}
      </p>
    </div>
  );
}
