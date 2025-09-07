import { useState } from 'react';
import { useI18n } from '@/hooks/use-i18n';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Minus, Plus } from 'lucide-react';

interface ProductQuantitySelectorProps {
  maxQuantity: number;
  onChange: (quantity: number) => void;
}

export default function ProductQuantitySelector({ maxQuantity, onChange }: ProductQuantitySelectorProps) {
  const { t } = useI18n();
  const [quantity, setQuantity] = useState(1);

  const handleQuantityChange = (newQuantity: number) => {
    // Ensure quantity is within valid range
    const validQuantity = Math.max(1, Math.min(newQuantity, maxQuantity));
    setQuantity(validQuantity);
    onChange(validQuantity);
  };

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const value = parseInt(e.target.value);
    if (!isNaN(value)) {
      handleQuantityChange(value);
    }
  };

  return (
    <div className="flex flex-col gap-2">
      <label htmlFor="quantity" className="font-medium">
        {t('quantity', 'Quantity')}
      </label>
      <div className="flex items-center">
        <Button
          type="button"
          variant="outline"
          size="icon"
          onClick={() => handleQuantityChange(quantity - 1)}
          disabled={quantity <= 1}
          className="ltr:rounded-r-none rtl:rounded-l-none"
        >
          <Minus className="h-4 w-4" />
        </Button>
        <Input
          id="quantity"
          type="number"
          value={quantity}
          onChange={handleInputChange}
          min={1}
          max={maxQuantity}
          className="w-16 text-center rounded-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
        />
        <Button
          type="button"
          variant="outline"
          size="icon"
          onClick={() => handleQuantityChange(quantity + 1)}
          disabled={quantity >= maxQuantity}
          className="ltr:rounded-l-none rtl:rounded-r-none"
        >
          <Plus className="h-4 w-4" />
        </Button>
      </div>
    </div>
  );
}
