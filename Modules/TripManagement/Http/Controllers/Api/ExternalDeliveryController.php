<?php

namespace Modules\TripManagement\Http\Controllers\Api;

use App\Models\Partner;
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
        $signature = $request->header('X-Webhook-Signature');
        $payload = $request->all();
        $rawBody = $request->getContent();

        $result = $this->externalDeliveryService->handleWebhook($provider, $payload, $signature, $rawBody);

        return response()->json($result, $result['accepted'] ?? false ? 200 : 422);
    }

    public function webhookByPartner(Request $request, Partner $partner): JsonResponse
    {
        $this->externalDeliveryService->setPartner($partner);

        $signature = $request->header('X-Webhook-Signature');
        $payload = $request->all();
        $rawBody = $request->getContent();

        $provider = $partner->company_name ? str_slug($partner->company_name) : 'xerin_marketplace';

        $result = $this->externalDeliveryService->handleWebhook($provider, $payload, $signature, $rawBody);

        return response()->json($result, $result['accepted'] ?? false ? 200 : 422);
    }
}
