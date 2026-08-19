<?php

namespace App\Listeners;

use App\Events\OrderSmsNotificationEvent;
use Illuminate\Support\Facades\Log;
use Modules\Gateways\Traits\SmsGateway;

class SendOrderSmsListener
{
    use SmsGateway;

    /**
     * Handle the event.
     */
    public function handle(OrderSmsNotificationEvent $event): void
    {
        $trip = $event->trip;
        $status = $event->status;

        $customer = $trip->customer;
        if (!$customer) {
            Log::info('OrderSms: no customer for trip ' . $trip->id);
            return;
        }

        $phone = $event->recipientPhone ?? $customer->phone;
        $name = $event->recipientName ?? trim(($customer->first_name ?? '') . ' ' . ($customer->last_name ?? ''));
        $trackingLink = $event->trackingLink ?? ($trip->type == PARCEL ? route('track-parcel', $trip->ref_id) : null);
        $parcelId = $trip->ref_id;

        if (!$phone) {
            Log::info('OrderSms: no phone for trip ' . $trip->id);
            return;
        }

        $message = $this->buildMessage($status, $name, $parcelId, $trackingLink, $trip);

        if (!$message) {
            Log::info('OrderSms: no template for status ' . $status);
            return;
        }

        try {
            $result = self::sendMessage($phone, $message);
            Log::info('OrderSms: sent to ' . $phone . ' status=' . $status . ' result=' . $result);
        } catch (\Exception $e) {
            Log::error('OrderSms: failed for trip ' . $trip->id . ': ' . $e->getMessage());
        }
    }

    /**
     * Build SMS message based on order status.
     */
    private function buildMessage(string $status, string $customerName, string $parcelId, ?string $trackingLink, $trip): ?string
    {
        $businessName = businessConfig('business_name', 'business_information')?->value ?? 'Xerin Express';

        $messages = [
            'created' => "Hello {CustomerName}, your order #{ParcelId} has been placed successfully with {BusinessName}. We are finding a delivery partner for you.",
            'accepted' => "Hello {CustomerName}, good news! A delivery partner has been assigned to your order #{ParcelId}. Track your delivery here: {TrackingLink}",
            'ongoing' => "Hello {CustomerName}, your order #{ParcelId} is now on the way. Track live here: {TrackingLink}",
            'completed' => "Hello {CustomerName}, your order #{ParcelId} has been completed successfully. Thank you for choosing {BusinessName}.",
            'cancelled' => "Hello {CustomerName}, your order #{ParcelId} has been cancelled. For assistance, contact {BusinessName}.",
            'returned' => "Hello {CustomerName}, your order #{ParcelId} has been returned. Track here: {TrackingLink}",
        ];

        $template = $messages[$status] ?? null;
        if (!$template) {
            return null;
        }

        $message = str_replace('{CustomerName}', $customerName ?: 'Customer', $template);
        $message = str_replace('{ParcelId}', $parcelId, $message);
        $message = str_replace('{BusinessName}', $businessName, $message);
        $message = str_replace('{TrackingLink}', $trackingLink ?? '', $message);

        return $message;
    }
}
