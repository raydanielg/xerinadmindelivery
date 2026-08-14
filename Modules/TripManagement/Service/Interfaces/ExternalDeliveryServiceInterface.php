<?php

namespace Modules\TripManagement\Service\Interfaces;

interface ExternalDeliveryServiceInterface
{
    public function quoteDelivery(array $data): array;
    public function getDelivery(string $sellerOrderId): array;
    public function requestDelivery(string $sellerOrderId): array;
    public function handleWebhook(string $provider, array $payload, ?string $signature): array;
}
