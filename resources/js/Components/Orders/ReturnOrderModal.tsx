import React, { useState } from 'react';
import { useForm } from '@inertiajs/react';
import { useI18n } from '@/hooks/use-i18n';
import { Button } from '@/Components/ui/button';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/Components/ui/dialog';
import { AlertCircle, RotateCcw } from 'lucide-react';
import { Alert, AlertDescription } from '@/Components/ui/alert';

interface ReturnOrderModalProps {
  orderId: number;
  canRequestReturn: boolean;
}

export default function ReturnOrderModal({ orderId, canRequestReturn }: ReturnOrderModalProps) {
  const [open, setOpen] = useState(false);
  const { t } = useI18n();

  const { data, setData, post, processing, errors, reset } = useForm({
    reason: '',
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();

    post(route('orders.return.request', orderId), {
      onSuccess: () => {
        setOpen(false);
        reset();
      },
    });
  };

  if (!canRequestReturn) {
    return null;
  }

  return (
    <Dialog open={open} onOpenChange={setOpen}>      <DialogTrigger asChild>
        <Button variant="outline" className="flex items-center gap-2">
          <RotateCcw className="h-4 w-4" />
          {t("request_return", "Request Return")}
        </Button>
      </DialogTrigger>
      <DialogContent className="sm:max-w-[425px]">
        <DialogHeader>
          <DialogTitle>{t("request_order_return", "Request Order Return")}</DialogTitle>
          <DialogDescription>
            {t("return_request_description", "Please specify the reason for the return request. Your request will be reviewed by our customer service team.")}
          </DialogDescription>
        </DialogHeader>

        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="space-y-2">
            <Label htmlFor="reason">{t("return_reason", "Return Reason")}</Label>
            <Textarea
              id="reason"
              value={data.reason}
              onChange={(e) => setData('reason', e.target.value)}
              placeholder={t("return_reason_placeholder", "Write the reason for the return request here...")}
              rows={4}
              className="resize-none"
            />
            {errors.reason && (
              <p className="text-sm text-destructive">{errors.reason}</p>
            )}
          </div>

          <Alert>
            <AlertCircle className="h-4 w-4" />
            <AlertDescription>
              {t("return_policy_notice", "Returns can be requested within 14 days of delivery. Your request will be reviewed within 24-48 hours.")}
            </AlertDescription>
          </Alert>

          <DialogFooter className="flex gap-2">
            <Button
              type="button"
              variant="outline"
              onClick={() => setOpen(false)}
              disabled={processing}
            >
              {t("cancel", "Cancel")}
            </Button>
            <Button type="submit" disabled={processing || !data.reason.trim()}>
              {processing ? t("sending", "Sending...") : t("submit_return_request", "Submit Return Request")}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  );
}
