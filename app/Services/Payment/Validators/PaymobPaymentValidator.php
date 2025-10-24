<?php

namespace App\Services\Payment\Validators;

use App\Interfaces\PaymentValidatorInterface;
use Illuminate\Support\Facades\Log;

/**
 * Paymob-specific payment validator
 * Follows Single Responsibility Principle - only handles Paymob validation
 * Uses HMAC-SHA512 for webhook validation
 */
class PaymobPaymentValidator implements PaymentValidatorInterface
{
    protected string $hmac;

    public function __construct(?string $secretKey = null)
    {
        $this->hmac =  config('services.paymob.hmac_secret');
    }

    /**
     * Validate the payment response from Paymob
     */
    public function validatePaymentResponse(array $params): bool
    {
        Log::info('Validating Paymob payment response', [
            'params' => $params,
            'param_keys' => array_keys($params),
        ]);

        // For webhook data validation
        if (isset($params['obj'])) {
            return $this->validateWebhookResponseData($params);
        }

        // For URL redirect parameters - be lenient
        if (isset($params['success']) || isset($params['id'])) {
            Log::info('Treating as URL redirect parameters from Paymob', ['params' => $params]);
            return true; // Rely on webhook for actual validation
        }

        Log::warning('Unknown Paymob response format', ['params' => $params]);
        return false;
    }

    /**
     * Validate webhook response data structure
     */
    private function validateWebhookResponseData(array $data): bool
    {
        // Required fields for a valid webhook payment response
        if (!isset($data['obj'])) {
            Log::warning('Missing obj in Paymob webhook data');
            return false;
        }

        $obj = $data['obj'];
        $requiredFields = ['id', 'success', 'amount_cents', 'order'];

        foreach ($requiredFields as $field) {
            if (!isset($obj[$field])) {
                Log::warning('Missing required field in Paymob webhook obj', [
                    'missing_field' => $field,
                    'available_fields' => array_keys($obj),
                ]);
                return false;
            }
        }

        return true;
    }

    /**
     * Validate the webhook payload from Paymob using HMAC-SHA512
     */
    public function validateWebhookPayload(string $rawPayload, array $headers, array $queryParams = []): bool
    {
        try {
            $jsonData = json_decode($rawPayload, true);

            if (!$jsonData || !isset($jsonData['obj'])) {
                Log::warning('Invalid Paymob webhook JSON structure');
                return false;
            }

            $obj = $jsonData['obj'];

            // Paymob sends HMAC in query string parameter
            $receivedHmac = $queryParams['hmac'] ?? null;

            // Fallback: check headers
            if (!$receivedHmac) {
                $headers = array_change_key_case($headers, CASE_LOWER);
                $receivedHmac = $headers['x-paymob-hmac'] ?? null;
            }

            // Fallback: check if it's in the data itself
            if (!$receivedHmac && isset($jsonData['hmac'])) {
                $receivedHmac = $jsonData['hmac'];
            }

            if (is_array($receivedHmac)) {
                $receivedHmac = $receivedHmac[0] ?? null;
            }

            if (!$receivedHmac) {
                Log::warning('No HMAC found in Paymob webhook', [
                    'query_params' => array_keys($queryParams),
                    'headers' => array_keys($headers),
                    'has_hmac_in_data' => isset($jsonData['hmac']),
                ]);
                return false;
            }

            // Build concatenated string according to Paymob documentation
            // Order matters! These fields must be in this exact order
            // Boolean values must be converted to string "true" or "false"
            $concatenatedString = implode('', [
                $obj['amount_cents'] ?? '',
                $obj['created_at'] ?? '',
                $obj['currency'] ?? '',
                $this->boolToString($obj['error_occured'] ?? false),
                $this->boolToString($obj['has_parent_transaction'] ?? false),
                $obj['id'] ?? '',
                $obj['integration_id'] ?? '',
                $this->boolToString($obj['is_3d_secure'] ?? false),
                $this->boolToString($obj['is_auth'] ?? false),
                $this->boolToString($obj['is_capture'] ?? false),
                $this->boolToString($obj['is_refunded'] ?? false),
                $this->boolToString($obj['is_standalone_payment'] ?? false),
                $this->boolToString($obj['is_voided'] ?? false),
                $obj['order']['id'] ?? '',
                $obj['owner'] ?? '',
                $this->boolToString($obj['pending'] ?? false),
                $obj['source_data']['pan'] ?? 'NA',
                $obj['source_data']['sub_type'] ?? 'NA',
                $obj['source_data']['type'] ?? 'NA',
                $this->boolToString($obj['success'] ?? false),
            ]);

            // Calculate expected HMAC using SHA512
            $expectedHmac = hash_hmac('sha512', $concatenatedString, $this->hmac);

            $isValid = hash_equals($expectedHmac, $receivedHmac);

            if (!$isValid) {
                Log::warning('Paymob webhook HMAC validation failed', [
                    'expected' => $expectedHmac,
                    'received' => $receivedHmac,
                    'concatenated_string' => $concatenatedString,
                    'concatenated_string_length' => strlen($concatenatedString),
                    'secret_key_length' => strlen($this->hmac),
                ]);
            } else {
                Log::info('Paymob webhook HMAC validation successful');
            }

            return $isValid;

        } catch (\Exception $e) {
            Log::error('Error validating Paymob webhook signature', [
                'exception' => $e->getMessage(),
                'raw_payload_length' => strlen($rawPayload),
            ]);

            return false;
        }
    }

    /**
     * Convert boolean to string "true" or "false" for HMAC calculation
     * Paymob expects boolean values as lowercase strings
     */
    private function boolToString($value): string
    {
        if ($value === true || $value === 'true') {
            return 'true';
        }
        return 'false';
    }
}
