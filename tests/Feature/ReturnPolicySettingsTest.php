<?php

use App\Models\ReturnPolicySetting;
use App\Models\User;
use App\Services\ReturnPolicyService;
use Carbon\Carbon;

beforeEach(function () {
    // Clean up settings before each test
    ReturnPolicySetting::truncate();

    // Create admin user
    $this->admin = User::factory()->create();
    // Assuming you have a role system, adjust as needed
    // $this->admin->assignRole('admin');

    $this->actingAs($this->admin);
});

it('can initialize default return policy settings', function () {
    ReturnPolicyService::initializeDefaults();

    expect(ReturnPolicySetting::count())->toBe(10);
    expect(ReturnPolicyService::returnsEnabled())->toBeTrue();
    expect(ReturnPolicyService::returnWindowDays())->toBe(30);
    expect(ReturnPolicyService::requiresAdminApproval())->toBeTrue();
});

it('can get and set individual settings', function () {
    // Test setting a value
    ReturnPolicyService::set('test_setting', 'test_value');
    expect(ReturnPolicyService::get('test_setting'))->toBe('test_value');

    // Test setting array value
    ReturnPolicyService::set('test_array', ['item1', 'item2']);
    expect(ReturnPolicyService::get('test_array'))->toBe(['item1', 'item2']);

    // Test default value
    expect(ReturnPolicyService::get('nonexistent', 'default'))->toBe('default');
});

it('can check if order can be returned based on policy', function () {
    ReturnPolicyService::initializeDefaults();

    // Test with order within return window
    $recentDate = Carbon::now()->subDays(15);
    expect(ReturnPolicyService::canReturn($recentDate))->toBeTrue();

    // Test with order outside return window
    $oldDate = Carbon::now()->subDays(45);
    expect(ReturnPolicyService::canReturn($oldDate))->toBeFalse();

    // Test when returns are disabled
    ReturnPolicyService::set('returns_enabled', false);
    ReturnPolicyService::clearCache(); // Clear cache to get updated setting
    expect(ReturnPolicyService::canReturn($recentDate))->toBeFalse();
});

it('can validate return reasons', function () {
    ReturnPolicyService::initializeDefaults();

    expect(ReturnPolicyService::isValidReturnReason('defective'))->toBeTrue();
    expect(ReturnPolicyService::isValidReturnReason('changed_mind'))->toBeTrue();
    expect(ReturnPolicyService::isValidReturnReason('invalid_reason'))->toBeFalse();
});

it('can get return reason labels', function () {
    $labels = ReturnPolicyService::getReturnReasonLabels();

    expect($labels)->toBeArray();
    expect($labels['defective'])->toBe('Defective/Damaged');
    expect($labels['wrong_item'])->toBe('Wrong Item Received');

    expect(ReturnPolicyService::getReturnReasonLabel('defective'))->toBe('Defective/Damaged');
    expect(ReturnPolicyService::getReturnReasonLabel('unknown_reason'))->toBe('Unknown reason');
});

it('can calculate return deadline', function () {
    ReturnPolicyService::initializeDefaults();

    $orderDate = Carbon::now()->subDays(10);
    $deadline = ReturnPolicyService::getReturnDeadline($orderDate);

    // The deadline should be 30 days after the order date
    $expectedDeadline = $orderDate->copy()->addDays(30);
    expect($deadline->format('Y-m-d'))->toBe($expectedDeadline->format('Y-m-d'));
});

it('can get public settings for frontend', function () {
    ReturnPolicyService::initializeDefaults();

    $publicSettings = ReturnPolicyService::getPublicSettings();

    expect($publicSettings)->toBeArray();
    expect($publicSettings)->toHaveKey('returns_enabled');
    expect($publicSettings)->toHaveKey('return_window_days');
    expect($publicSettings)->toHaveKey('allowed_return_reasons');
    expect($publicSettings)->toHaveKey('return_reason_labels');
});

it('handles JSON encoding and decoding of array settings', function () {
    $arrayValue = ['option1', 'option2', 'option3'];

    ReturnPolicyService::set('test_array_setting', $arrayValue);

    // Verify it was stored as JSON
    $setting = ReturnPolicySetting::where('key', 'test_array_setting')->first();
    expect($setting->value)->toBeString();
    expect(json_decode($setting->value, true))->toBe($arrayValue);

    // Verify retrieval returns array
    expect(ReturnPolicyService::get('test_array_setting'))->toBe($arrayValue);
});

it('caches settings properly', function () {
    ReturnPolicyService::set('cache_test', 'initial_value');

    // First call should load from database
    expect(ReturnPolicyService::get('cache_test'))->toBe('initial_value');

    // Directly update database to test caching
    ReturnPolicySetting::where('key', 'cache_test')->update(['value' => 'updated_value']);

    // Should still return cached value
    expect(ReturnPolicyService::get('cache_test'))->toBe('initial_value');

    // Clear cache and verify updated value
    ReturnPolicyService::clearCache();
    expect(ReturnPolicyService::get('cache_test'))->toBe('updated_value');
});
test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
