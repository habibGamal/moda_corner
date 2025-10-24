<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class InstaPayController extends Controller
{
    /**
     * Show the InstaPay upload form
     */
    public function show(Order $order)
    {
        // Verify the order belongs to the authenticated user
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this order.');
        }

        // Verify the payment method is InstaPay
        if ($order->payment_method !== PaymentMethod::INSTAPAY) {
            return redirect()->route('orders.show', $order->id)
                ->with('error', 'هذا الطلب لا يستخدم طريقة الدفع عبر إنستاباي.');
        }

        // Check if payment already confirmed
        if ($order->payment_status === PaymentStatus::PAID) {
            return redirect()->route('orders.show', $order->id)
                ->with('info', 'تم تأكيد الدفع بالفعل لهذا الطلب.');
        }

        // Check if can reupload
        $paymentDetails = json_decode($order->payment_details, true) ?? [];
        $canReupload = $paymentDetails['can_reupload'] ?? false;

        return Inertia::render('Payments/InstaPay', [
            'order' => $order->load('items.product', 'shippingAddress'),
            'canReupload' => $canReupload,
        ]);
    }

    /**
     * Store the payment proof upload
     */
    public function store(Request $request, Order $order)
    {
        // Verify the order belongs to the authenticated user
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this order.');
        }

        // Verify the payment method is InstaPay
        if ($order->payment_method !== PaymentMethod::INSTAPAY) {
            return redirect()->route('orders.show', $order->id)
                ->with('error', 'هذا الطلب لا يستخدم طريقة الدفع عبر إنستاباي.');
        }

        // Validate the request
        $validated = $request->validate([
            'instapay_account' => 'nullable|string|max:255',
            'payment_proof' => 'required|image|max:10240', // 10MB max
        ], [
            'payment_proof.required' => 'يجب إرفاق صورة إثبات الدفع.',
            'payment_proof.image' => 'يجب أن يكون الملف صورة.',
            'payment_proof.max' => 'حجم الصورة يجب ألا يتجاوز 10 ميجابايت.',
        ]);

        try {
            // Delete old payment proof if exists
            if ($order->payment_proof) {
                Storage::disk('public')->delete($order->payment_proof);
            }

            // Store the new payment proof image
            $path = $request->file('payment_proof')->store('payment-proofs', 'public');

            // Update order with payment proof and details
            $order->payment_proof = $path;
            $order->payment_status = PaymentStatus::IN_REVIEW;
            $order->payment_details = json_encode([
                'instapay_account' => $validated['instapay_account'] ?? null,
                'uploaded_at' => now()->toISOString(),
                'uploaded_by' => auth()->id(),
            ]);
            $order->save();

            // Send email notification to admin
            $this->notifyAdmin($order);

            Log::info('InstaPay payment proof uploaded', [
                'order_id' => $order->id,
                'user_id' => auth()->id(),
                'payment_proof' => $path,
            ]);

            return redirect()->route('orders.show', $order->id)
                ->with('success', 'تم رفع إثبات الدفع بنجاح. سيتم مراجعته والتأكيد عليه قريباً.');
        } catch (\Exception $e) {
            Log::error('Failed to upload InstaPay payment proof', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'حدث خطأ أثناء رفع إثبات الدفع. يرجى المحاولة مرة أخرى.');
        }
    }

    /**
     * Handle payment proof re-upload
     */
    public function reupload(Request $request, Order $order)
    {
        // Verify the order belongs to the authenticated user
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this order.');
        }

        // Verify the payment method is InstaPay
        if ($order->payment_method !== PaymentMethod::INSTAPAY) {
            return redirect()->route('orders.show', $order->id)
                ->with('error', 'هذا الطلب لا يستخدم طريقة الدفع عبر إنستاباي.');
        }

        // Check if already confirmed
        if ($order->payment_status === PaymentStatus::PAID) {
            return redirect()->route('orders.show', $order->id)
                ->with('info', 'تم تأكيد الدفع بالفعل لهذا الطلب.');
        }

        // Validate the request
        $validated = $request->validate([
            'instapay_account' => 'nullable|string|max:255',
            'payment_proof' => 'required|image|max:10240', // 10MB max
        ], [
            'payment_proof.required' => 'يجب إرفاق صورة إثبات الدفع.',
            'payment_proof.image' => 'يجب أن يكون الملف صورة.',
            'payment_proof.max' => 'حجم الصورة يجب ألا يتجاوز 10 ميجابايت.',
        ]);

        try {
            // Delete old payment proof if exists
            if ($order->payment_proof) {
                Storage::disk('public')->delete($order->payment_proof);
            }

            // Store the new payment proof image
            $path = $request->file('payment_proof')->store('payment-proofs', 'public');

            // Get existing details and update
            $existingDetails = json_decode($order->payment_details, true) ?? [];
            // Update order with new payment proof
            $order->payment_proof = $path;
            $order->payment_status = PaymentStatus::IN_REVIEW;
            $order->payment_details = json_encode(array_merge($existingDetails, [
                'instapay_account' => $validated['instapay_account'] ?? null,
                'reuploaded_at' => now()->toISOString(),
                'reuploaded_by' => auth()->id(),
                'can_reupload' => false,
                'rejected_at' => null, // Clear rejection
            ]));
            $order->save();
            $order->save();

            // Send email notification to admin
            $this->notifyAdmin($order, true);

            Log::info('InstaPay payment proof re-uploaded', [
                'order_id' => $order->id,
                'user_id' => auth()->id(),
                'payment_proof' => $path,
            ]);

            return redirect()->route('orders.show', $order->id)
                ->with('success', 'تم إعادة رفع إثبات الدفع بنجاح. سيتم مراجعته والتأكيد عليه قريباً.');
        } catch (\Exception $e) {
            Log::error('Failed to re-upload InstaPay payment proof', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'حدث خطأ أثناء إعادة رفع إثبات الدفع. يرجى المحاولة مرة أخرى.');
        }
    }

    /**
     * Send email notification to admin
     */
    protected function notifyAdmin(Order $order, bool $isReupload = false)
    {
        try {
            // Get admin users (you can customize this query)
            $adminEmails = User::where('is_admin', true)
                ->pluck('email')
                ->toArray();

            if (empty($adminEmails)) {
                // Fallback to a default admin email from config
                $adminEmails = [config('mail.admin_email', 'admin@example.com')];
            }

            $subject = $isReupload
                ? "إعادة رفع إثبات دفع - طلب #{$order->id}"
                : "إثبات دفع جديد يحتاج مراجعة - طلب #{$order->id}";

            $message = $isReupload
                ? "تم إعادة رفع إثبات دفع للطلب رقم #{$order->id}. يرجى مراجعته."
                : "تم رفع إثبات دفع جديد للطلب رقم #{$order->id}. يرجى مراجعته.";

            Mail::raw($message, function ($mail) use ($adminEmails, $subject) {
                $mail->to($adminEmails)
                    ->subject($subject);
            });

            Log::info('Admin notification sent for InstaPay payment proof', [
                'order_id' => $order->id,
                'is_reupload' => $isReupload,
            ]);
        } catch (\Exception $e) {
            // Log but don't fail the upload process
            Log::error('Failed to send admin notification for InstaPay', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
