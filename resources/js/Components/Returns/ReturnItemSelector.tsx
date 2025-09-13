import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Checkbox } from '@/Components/ui/checkbox';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { useI18n } from '@/hooks/use-i18n';
import { App } from '@/types';
import { AlertCircle, Minus, Plus, Package } from 'lucide-react';

interface ReturnItemSelectorProps {
  returnableItems: (App.Models.OrderItem & {
    max_returnable_quantity: number;
  })[];
  selectedItems: Array<{
    order_item_id: number;
    quantity: number;
  }>;
  onItemToggle: (itemId: number, maxQuantity: number) => void;
  onQuantityChange: (itemId: number, quantity: number) => void;
  errors?: { [key: string]: string };
}

export default function ReturnItemSelector({
  returnableItems,
  selectedItems,
  onItemToggle,
  onQuantityChange,
  errors
}: ReturnItemSelectorProps) {
  const { t, getLocalizedField } = useI18n();

  return (
    <Card>
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <Package className="h-5 w-5" />
          {t("select_items_to_return", "Select Items to Return")}
        </CardTitle>
        <p className="text-sm text-muted-foreground">
          {t(
            "select_items_description",
            "Choose the items you want to return and specify the quantity for each item."
          )}
        </p>
      </CardHeader>
      <CardContent>
        {returnableItems.length === 0 ? (
          <Alert>
            <AlertCircle className="h-4 w-4" />
            <AlertDescription>
              {t(
                "no_eligible_items",
                "No items in this order are eligible for return."
              )}
            </AlertDescription>
          </Alert>
        ) : (
          <div className="space-y-4">
            {returnableItems.map((item) => {
              const returnItem = selectedItems.find(
                (ri) => ri.order_item_id === item.id
              );
              const isSelected = !!returnItem;
              const selectedQuantity = returnItem?.quantity || 0;

              return (
                <div
                  key={item.id}
                  className={`p-4 border rounded-lg transition-colors ${
                    isSelected
                      ? "border-primary bg-primary/5"
                      : "border-gray-200 dark:border-gray-700"
                  }`}
                >
                  <div className="flex items-start gap-3">
                    <Checkbox
                      checked={isSelected}
                      onCheckedChange={() =>
                        onItemToggle(item.id, item.max_returnable_quantity)
                      }
                      className="mt-1"
                    />
                    <div className="flex-1">
                      <div className="flex items-center justify-between">
                        <div>
                          <h5 className="font-medium text-sm">
                            {getLocalizedField(item.product, 'name')}
                          </h5>
                          {item.variant && (
                            <div className="text-xs text-muted-foreground mt-1 space-x-2 rtl:space-x-reverse">
                              {item.variant.color && (
                                <span>{t("color", "Color")}: {item.variant.color}</span>
                              )}
                              {item.variant.size && (
                                <span>{t("size", "Size")}: {item.variant.size}</span>
                              )}
                              {item.variant.capacity && (
                                <span>{t("capacity", "Capacity")}: {item.variant.capacity}</span>
                              )}
                            </div>
                          )}
                          <div className="text-xs text-muted-foreground mt-1">
                            {t("unit_price", "Unit Price")}: EGP {Number(item.unit_price).toFixed(2)}
                          </div>
                        </div>
                        <div className="text-right">
                          <div className="text-sm font-medium">
                            EGP {Number(item.unit_price * item.quantity).toFixed(2)}
                          </div>
                          <div className="text-xs text-muted-foreground">
                            {t("quantity", "Qty")}: {item.quantity}
                          </div>
                        </div>
                      </div>

                      {/* Quantity Selector - only show when item is selected */}
                      {isSelected && (
                        <div className="mt-4 pt-3 border-t border-gray-200 dark:border-gray-700">
                          <Label className="text-sm font-medium">
                            {t("quantity", "Quantity")} {t("return_reason", "Return")}
                          </Label>
                          <div className="flex items-center gap-2 mt-2">
                            <Button
                              type="button"
                              variant="outline"
                              size="sm"
                              onClick={() => onQuantityChange(item.id, selectedQuantity - 1)}
                              disabled={selectedQuantity <= 1}
                              className="h-8 w-8 p-0"
                            >
                              <Minus className="h-4 w-4" />
                            </Button>
                            <Input
                              type="number"
                              min="1"
                              max={item.max_returnable_quantity}
                              value={selectedQuantity}
                              onChange={(e) => onQuantityChange(item.id, parseInt(e.target.value) || 0)}
                              className="w-20 text-center h-8"
                            />
                            <Button
                              type="button"
                              variant="outline"
                              size="sm"
                              onClick={() => onQuantityChange(item.id, selectedQuantity + 1)}
                              disabled={selectedQuantity >= item.max_returnable_quantity}
                              className="h-8 w-8 p-0"
                            >
                              <Plus className="h-4 w-4" />
                            </Button>
                            <span className="text-xs text-muted-foreground ltr:ml-2 rtl:mr-2">
                              {t("max_returnable_quantity", "Max")}: {item.max_returnable_quantity}
                            </span>
                          </div>
                        </div>
                      )}
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
        )}

        {errors?.items && (
          <Alert className="mt-4">
            <AlertCircle className="h-4 w-4" />
            <AlertDescription>
              {errors.items}
            </AlertDescription>
          </Alert>
        )}
      </CardContent>
    </Card>
  );
}
