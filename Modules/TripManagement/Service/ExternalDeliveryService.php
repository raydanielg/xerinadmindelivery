<?php

namespace Modules\TripManagement\Service;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\TripManagement\Entities\ExternalDelivery;
use Modules\TripManagement\Service\Interfaces\ExternalDeliveryServiceInterface;

class ExternalDeliveryService implements ExternalDeliveryServiceInterface
{
    private string $baseUrl;
    private string $apiKey;
    private string $provider;

    public function __construct()
    {
        $this->baseUrl = config('external_delivery.base_url', 'https://api.xerinmarketplace.com');
        $this->apiKey = config('external_delivery.api_key', '');
        $this->provider = config('external_delivery.provider', 'xerin_marketplace');
    }

    public function quoteDelivery(array $data): array
    {
        try {
            $response = Http::withToken($this->apiKey)
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
            $response = Http::withToken($this->apiKey)
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
            $response = Http::withToken($this->apiKey)
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

    public function handleWebhook(string $provider, array $payload, ?string $signature): array
    {
        try {
            $deliveryId = $payload['delivery_id'] ?? null;
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
}
