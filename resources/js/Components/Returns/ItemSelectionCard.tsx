import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Checkbox } from '@/Components/ui/checkbox';
import { Badge } from '@/Components/ui/badge';
import { Label } from '@/Components/ui/label';
import { Button } from '@/Components/ui/button';
import { useI18n } from '@/hooks/use-i18n';
import { AlertCircle, Minus, Plus } from 'lucide-react';
import { App } from '@/types';

interface ItemSelectionCardProps {
  returnableItems: (App.Models.OrderItem & {
    max_returnable_quantity: number;
  })[];
  selectedItems: Array<{
    order_item_id: number;
    quantity: number;
  }>;
  onItemToggle: (itemId: number, maxQuantity: number) => void;
  onQuantityChange: (itemId: number, quantity: number) => void;
  error?: string;
}

export function ItemSelectionCard({
  returnableItems,
  selectedItems,
  onItemToggle,
  onQuantityChange,
  error
}: ItemSelectionCardProps) {
  const { t, getLocalizedField } = useI18n();

  return (
    <Card>
      <CardHeader>
        <CardTitle>
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
                      : "border-gray-200"
                  }`}
                >
                  <div className="flex items-start gap-3">
                    <Checkbox
                      checked={isSelected}
                      onCheckedChange={() =>
                        onItemToggle(item.id, item.quantity)
                      }
                    />
                    <div className="flex-1">
                      <div className="flex items-center justify-between">
                        <h4 className="font-medium">
                          {getLocalizedField(item.product, "name")}
                        </h4>
                        <Badge variant="outline">
                          EGP {Number(item.unit_price).toFixed(2)}
                        </Badge>
                      </div>

                      {item.variant && (
                        <div className="text-sm text-muted-foreground mt-1 space-x-2 rtl:space-x-reverse">
                          {item.variant.color && (
                            <span>
                              {t("color", "Color")}: {item.variant.color}
                            </span>
                          )}
                          {item.variant.size && (
                            <span>
                              {t("size", "Size")}: {item.variant.size}
                            </span>
                          )}
                          {item.variant.capacity && (
                            <span>
                              {t("capacity", "Capacity")}: {item.variant.capacity}
                            </span>
                          )}
                        </div>
                      )}

                      <div className="flex items-center justify-between mt-2">
                        <span className="text-sm text-muted-foreground">
                          {t("ordered_quantity", "Ordered")}: {item.quantity}
                        </span>

                        {isSelected && (
                          <div className="flex items-center gap-2">
                            <Label className="text-sm">
                              {t("return_quantity", "Return Qty")}:
                            </Label>
                            <div className="flex items-center gap-1">
                              <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                className="h-8 w-8 p-0"
                                onClick={() =>
                                  onQuantityChange(
                                    item.id,
                                    selectedQuantity - 1
                                  )
                                }
                                disabled={selectedQuantity <= 1}
                              >
                                <Minus className="h-3 w-3" />
                              </Button>
                              <span className="min-w-[3rem] text-center text-sm font-medium px-2">
                                {selectedQuantity}
                              </span>
                              <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                className="h-8 w-8 p-0"
                                onClick={() =>
                                  onQuantityChange(
                                    item.id,
                                    selectedQuantity + 1
                                  )
                                }
                                disabled={selectedQuantity >= item.quantity}
                              >
                                <Plus className="h-3 w-3" />
                              </Button>
                            </div>
                          </div>
                        )}
                      </div>
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
        )}

        {error && (
          <Alert className="mt-4">
            <AlertCircle className="h-4 w-4" />
            <AlertDescription>{error}</AlertDescription>
          </Alert>
        )}
      </CardContent>
    </Card>
  );
}
