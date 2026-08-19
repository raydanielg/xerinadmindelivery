<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\TripManagement\Entities\TripRequest;

class OrderSmsNotificationEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public TripRequest $trip;
    public string $status;
    public ?string $recipientPhone;
    public ?string $recipientName;
    public ?string $trackingLink;

    /**
     * Create a new event instance.
     *
     * @param TripRequest $trip
     * @param string $status The order status (created, accepted, ongoing, completed, cancelled, returned)
     * @param string|null $recipientPhone Override recipient phone (defaults to customer phone)
     * @param string|null $recipientName Override recipient name (defaults to customer name)
     * @param string|null $trackingLink Override tracking link (defaults to track-parcel route)
     */
    public function __construct(
        TripRequest $trip,
        string $status,
        ?string $recipientPhone = null,
        ?string $recipientName = null,
        ?string $trackingLink = null
    ) {
        $this->trip = $trip;
        $this->status = $status;
        $this->recipientPhone = $recipientPhone;
        $this->recipientName = $recipientName;
        $this->trackingLink = $trackingLink;
    }
}
