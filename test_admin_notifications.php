<?php

use App\Models\Order;
use App\Models\User;
use App\Services\AdminNotificationService;

// Test script to verify admin notifications work
// Run with: php artisan tinker

// Example usage:
// $order = Order::first();
// $service = app(AdminNotificationService::class);
// $service->sendOrderPlacedNotification($order);

// Create a test order (if needed)
// $user = User::first();
// $order = Order::factory()->create(['user_id' => $user->id]);

// Test the service
// app(AdminNotificationService::class)->sendOrderPlacedNotification($order);
// app(AdminNotificationService::class)->sendOrderReturnRequestNotification($order);

echo "Test file created. Use php artisan tinker to test the notifications manually.\n";
echo "Example commands:\n";
echo "\$order = Order::first();\n";
echo "\$service = app(App\\Services\\AdminNotificationService::class);\n";
echo "\$service->sendOrderPlacedNotification(\$order);\n";
echo "\$service->sendOrderReturnRequestNotification(\$order);\n";
