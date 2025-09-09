<?php

namespace App\Services\Payment\Validators;

use App\Interfaces\PaymentValidatorInterface;
use Illuminate\Support\Facades\Log;

/**
 * Kashier-specific payment validator
 * Follows Single Responsibility Principle - only handles Kashier validation
 */
class KashierPaymentValidator implements PaymentValidatorInterface
{
    protected string $apiKey;

    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey ?? config('services.kashier.api_key');
    }

    /**
     * Validate the payment response from Kashier
     */
    public function validatePaymentResponse(array $params): bool
    {
        // Log the incoming parameters for debugging
        Log::info('Validating payment response', [
            'params' => $params,
            'param_keys' => array_keys($params),
        ]);

        // For webhook data, we don't validate signatures here (already done in webhook handler)
        if (isset($params['status']) && isset($params['transactionId']) && isset($params['merchantOrderId'])) {
            // This looks like webhook data - basic structure validation
            return $this->validateWebhookResponseData($params);
        }

        // For URL redirect parameters - be more lenient
        if (isset($params['merchantOrderId']) || isset($params['orderReference'])) {
            // This looks like a success URL redirect - basic validation
            Log::info('Treating as URL redirect parameters', ['params' => $params]);
            return true; // We'll rely on the payment being processed via webhook
        }

        // For direct payment response data with signature
        if (! isset($params['signature'])) {
            Log::warning('No signature found in payment response', ['params' => $params]);
            return false;
        }

        $receivedSignature = $params['signature'];
        $queryString = '';

        foreach ($params as $key => $value) {
            if ($key === 'signature' || $key === 'mode') {
                continue;
            }
            $queryString .= "&{$key}={$value}";
        }

        $queryString = ltrim($queryString, '&');
        $expectedSignature = hash_hmac('sha256', $queryString, $this->apiKey);

        $isValid = hash_equals($expectedSignature, $receivedSignature);

        if (!$isValid) {
            Log::warning('Signature validation failed', [
                'expected' => $expectedSignature,
                'received' => $receivedSignature,
                'query_string' => $queryString,
            ]);
        }

        return $isValid;
    }

    /**
     * Validate webhook response data structure
     */
    private function validateWebhookResponseData(array $data): bool
    {
        // Required fields for a valid webhook payment response
        $requiredFields = ['status', 'merchantOrderId', 'transactionId'];

        foreach ($requiredFields as $field) {
            if (! isset($data[$field])) {
                Log::warning('Missing required field in webhook data', [
                    'missing_field' => $field,
                    'available_fields' => array_keys($data),
                ]);

                return false;
            }
        }

        // Check if status indicates success
        if ($data['status'] !== 'SUCCESS') {
            Log::info('Webhook data validation passed but status is not SUCCESS', [
                'status' => $data['status'],
            ]);
        }

        return true;
    }

    /**
     * Validate the webhook payload from Kashier
     */
    public function validateWebhookPayload(string $rawPayload, array $headers): bool
    {
        try {
            $jsonData = json_decode($rawPayload, true);

            if (! $jsonData || ! isset($jsonData['data']) || ! isset($jsonData['event'])) {
                return false;
            }

            $dataObj = $jsonData['data'];

            if (! isset($dataObj['signatureKeys']) || ! is_array($dataObj['signatureKeys'])) {
                return false;
            }

            $signatureKeys = $dataObj['signatureKeys'];
            sort($signatureKeys);

            $headers = array_change_key_case($headers, CASE_LOWER);
            $kashierSignature = $headers['x-kashier-signature'] ?? null;

            if (is_array($kashierSignature)) {
                $kashierSignature = $kashierSignature[0] ?? null;
            }

            if (! $kashierSignature) {
                return false;
            }

            $data = [];
            foreach ($signatureKeys as $key) {
                if (isset($dataObj[$key])) {
                    $data[$key] = $dataObj[$key];
                }
            }

            $queryString = http_build_query($data, '', '&', PHP_QUERY_RFC3986);
            $expectedSignature = hash_hmac('sha256', $queryString, $this->apiKey, false);

            Log::info('Kashier webhook signature validation', [
                'query_string' => $queryString,
                'expected_signature' => $expectedSignature,
                'received_signature' => $kashierSignature,
                'signature_keys' => $signatureKeys,
                'event' => $jsonData['event'],
            ]);

            return hash_equals($expectedSignature, $kashierSignature);

        } catch (\Exception $e) {
            Log::error('Error validating Kashier webhook signature', [
                'exception' => $e->getMessage(),
                'raw_payload' => $rawPayload,
            ]);

            return false;
        }
    }
}
