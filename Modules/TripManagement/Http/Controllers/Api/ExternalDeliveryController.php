<?php

namespace Modules\TripManagement\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\TripManagement\Service\Interfaces\ExternalDeliveryServiceInterface;

class ExternalDeliveryController extends Controller
{
    protected ExternalDeliveryServiceInterface $externalDeliveryService;

    public function __construct(ExternalDeliveryServiceInterface $externalDeliveryService)
    {
        $this->externalDeliveryService = $externalDeliveryService;
    }

    public function quote(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pickup' => 'required|array',
            'dropoff' => 'required|array',
            'package' => 'sometimes|array',
            'currency' => 'sometimes|string|max:3',
        ]);

        $result = $this->externalDeliveryService->quoteDelivery($validated);

        return response()->json($result, $result['success'] ? 200 : ($result['status'] ?? 500));
    }

    public function getDelivery(string $sellerOrderId): JsonResponse
    {
        $result = $this->externalDeliveryService->getDelivery($sellerOrderId);

        return response()->json($result, $result['success'] ? 200 : ($result['status'] ?? 500));
    }

    public function requestDelivery(string $sellerOrderId): JsonResponse
    {
        $result = $this->externalDeliveryService->requestDelivery($sellerOrderId);

        return response()->json($result, $result['success'] ? 200 : ($result['status'] ?? 500));
    }

    public function webhook(Request $request, string $provider): JsonResponse
    {
        $signature = $request->header('x-delivery-signature');
        $payload = $request->all();

        $result = $this->externalDeliveryService->handleWebhook($provider, $payload, $signature);

        return response()->json($result, $result['accepted'] ?? false ? 200 : 422);
    }
}
