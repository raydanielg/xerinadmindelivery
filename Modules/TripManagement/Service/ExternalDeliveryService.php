<?php

namespace Modules\TripManagement\Service;

use App\Models\Partner;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\TripManagement\Entities\ExternalDelivery;
use Modules\TripManagement\Service\Interfaces\ExternalDeliveryServiceInterface;

class ExternalDeliveryService implements ExternalDeliveryServiceInterface
{
    private string $baseUrl;
    private string $apiKey;
    private string $provider;
    private ?Partner $partner = null;

    public function __construct()
    {
        $this->baseUrl = config('external_delivery.base_url', 'https://api.xerinmarketplace.com');
        $this->apiKey = config('external_delivery.api_key', '');
        $this->provider = config('external_delivery.provider', 'xerin_marketplace');
    }

    public function setPartner(Partner $partner): void
    {
        $this->partner = $partner;
        $this->baseUrl = $partner->partner_api_base_url ?: $this->baseUrl;
        $this->provider = $partner->company_name ? str_slug($partner->company_name) : $this->provider;
    }

    public function quoteDelivery(array $data): array
    {
        try {
            $response = $this->buildHttpClient()
                ->timeout(30)
                ->post("{$this->baseUrl}/api/v1/delivery/quote", [
                    'pickup' => $data['pickup'] ?? [],
                    'dropoff' => $data['dropoff'] ?? [],
                    'package' => $data['package'] ?? [],
                    'currency' => $data['currency'] ?? 'TZS',
                ]);

            if ($response->successful()) {
                $body = $response->json();
                return [
                    'success' => true,
                    'data' => $body,
                ];
            }

            Log::error('External delivery quote failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to get delivery quote',
                'status' => $response->status(),
                'errors' => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error('External delivery quote exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function getDelivery(string $sellerOrderId): array
    {
        try {
            $response = $this->buildHttpClient()
                ->timeout(30)
                ->get("{$this->baseUrl}/api/v1/delivery/seller-orders/{$sellerOrderId}");

            if ($response->successful()) {
                $body = $response->json();
                $this->syncDeliveryRecord($body);
                return [
                    'success' => true,
                    'data' => $body,
                ];
            }

            Log::error('External delivery get failed', [
                'seller_order_id' => $sellerOrderId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to get delivery',
                'status' => $response->status(),
                'errors' => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error('External delivery get exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function requestDelivery(string $sellerOrderId): array
    {
        try {
            $response = $this->buildHttpClient()
                ->timeout(30)
                ->post("{$this->baseUrl}/api/v1/delivery/seller-orders/{$sellerOrderId}/request");

            if ($response->successful()) {
                $body = $response->json();
                $this->syncDeliveryRecord($body);
                return [
                    'success' => true,
                    'data' => $body,
                ];
            }

            Log::error('External delivery request failed', [
                'seller_order_id' => $sellerOrderId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to request delivery',
                'status' => $response->status(),
                'errors' => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error('External delivery request exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function handleWebhook(string $provider, array $payload, ?string $signature, string $rawBody = ''): array
    {
        try {
            if (!$this->verifySignature($rawBody, $signature)) {
                Log::warning('Webhook signature verification failed', [
                    'provider' => $provider,
                ]);
                return [
                    'accepted' => false,
                    'message' => 'Invalid signature',
                ];
            }

            $eventType = $payload['event'] ?? $payload['type'] ?? null;
            if ($eventType && !$this->isEventEnabled($eventType)) {
                return [
                    'accepted' => false,
                    'message' => "Event '{$eventType}' is not enabled",
                ];
            }

            $deliveryId = $payload['delivery_id'] ?? $payload['shipment_id'] ?? null;
            $status = $payload['status'] ?? null;

            if (!$deliveryId || !$status) {
                return [
                    'accepted' => false,
                    'message' => 'Missing delivery_id or status',
                ];
            }

            $delivery = ExternalDelivery::where('external_delivery_id', $deliveryId)
                ->orWhere('seller_order_id', $deliveryId)
                ->first();

            if ($delivery) {
                $delivery->update([
                    'status' => $status,
                    'last_synced_at' => now(),
                    'raw_response' => array_merge($delivery->raw_response ?? [], ['webhook' => $payload]),
                ]);
            } else {
                ExternalDelivery::create([
                    'partner_id' => $this->partner?->id,
                    'seller_order_id' => $deliveryId,
                    'provider' => $provider,
                    'external_delivery_id' => $deliveryId,
                    'status' => $status,
                    'last_synced_at' => now(),
                    'raw_response' => ['webhook' => $payload],
                ]);
            }

            return [
                'accepted' => true,
                'delivery_id' => $deliveryId,
                'status' => $status,
            ];
        } catch (\Exception $e) {
            Log::error('External delivery webhook exception', ['error' => $e->getMessage()]);
            return [
                'accepted' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    private function syncDeliveryRecord(array $data): void
    {
        try {
            $delivery = ExternalDelivery::where('seller_order_id', $data['seller_order_id'] ?? '')->first();

            if ($delivery) {
                $delivery->update([
                    'external_delivery_id' => $data['external_delivery_id'] ?? $delivery->external_delivery_id,
                    'status' => $data['status'] ?? $delivery->status,
                    'tracking_number' => $data['tracking_number'] ?? $delivery->tracking_number,
                    'tracking_url' => $data['tracking_url'] ?? $delivery->tracking_url,
                    'delivery_fee' => $data['delivery_fee'] ?? $delivery->delivery_fee,
                    'currency' => $data['currency'] ?? $delivery->currency,
                    'courier_name' => $data['courier_name'] ?? $delivery->courier_name,
                    'courier_phone' => $data['courier_phone'] ?? $delivery->courier_phone,
                    'estimated_pickup_at' => $data['estimated_pickup_at'] ?? $delivery->estimated_pickup_at,
                    'estimated_delivery_at' => $data['estimated_delivery_at'] ?? $delivery->estimated_delivery_at,
                    'last_synced_at' => now(),
                    'raw_response' => $data,
                ]);
            } else {
                ExternalDelivery::create([
                    'partner_id' => $this->partner?->id,
                    'shipment_id' => $data['shipment_id'] ?? null,
                    'seller_order_id' => $data['seller_order_id'],
                    'provider' => $data['provider'] ?? $this->provider,
                    'external_delivery_id' => $data['external_delivery_id'] ?? null,
                    'status' => $data['status'] ?? 'created',
                    'tracking_number' => $data['tracking_number'] ?? null,
                    'tracking_url' => $data['tracking_url'] ?? null,
                    'delivery_fee' => $data['delivery_fee'] ?? null,
                    'currency' => $data['currency'] ?? 'TZS',
                    'courier_name' => $data['courier_name'] ?? null,
                    'courier_phone' => $data['courier_phone'] ?? null,
                    'estimated_pickup_at' => $data['estimated_pickup_at'] ?? null,
                    'estimated_delivery_at' => $data['estimated_delivery_at'] ?? null,
                    'last_synced_at' => now(),
                    'raw_response' => $data,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('External delivery sync exception', ['error' => $e->getMessage()]);
        }
    }

    private function buildHttpClient()
    {
        $client = Http::timeout(30);

        if ($this->partner && $this->partner->auth_method === 'api_key') {
            $header = $this->partner->api_key_header ?: 'X-API-Key';
            $apiKey = $this->resolveCredential($this->partner->credential_reference);
            if ($apiKey) {
                $client = $client->withHeaders([$header => $apiKey]);
            }
        } else {
            $client = $client->withToken($this->apiKey);
        }

        return $client;
    }

    private function verifySignature(string $rawBody, ?string $signature): bool
    {
        if (!$signature) {
            return false;
        }

        $secret = config('external_delivery.webhook_secret', '');

        if (!$secret) {
            return false;
        }

        $expected = hash_hmac('sha256', $rawBody, $secret);

        return hash_equals($expected, $signature);
    }

    private function isEventEnabled(string $eventType): bool
    {
        $enabled = config('external_delivery.enabled_events', 'shipment.updated,delivery.completed');
        $events = array_filter(array_map('trim', explode(',', $enabled)));

        if (empty($events)) {
            return true;
        }

        return in_array($eventType, $events);
    }

    private function resolveCredential(?string $reference): ?string
    {
        if (!$reference) {
            return null;
        }

        if (!str_starts_with($reference, 'vault://')) {
            return $reference;
        }

        return config('external_delivery.api_key', '');
    }
}
