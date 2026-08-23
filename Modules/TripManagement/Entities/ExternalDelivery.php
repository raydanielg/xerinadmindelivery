<?php

namespace Modules\TripManagement\Entities;

use App\Models\Partner;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExternalDelivery extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'external_deliveries';

    protected $fillable = [
        'partner_id',
        'shipment_id',
        'seller_order_id',
        'provider',
        'external_delivery_id',
        'status',
        'tracking_number',
        'tracking_url',
        'delivery_fee',
        'currency',
        'courier_name',
        'courier_phone',
        'estimated_pickup_at',
        'estimated_delivery_at',
        'last_synced_at',
        'quote_id',
        'raw_response',
    ];

    protected $casts = [
        'delivery_fee' => 'decimal:2',
        'estimated_pickup_at' => 'datetime',
        'estimated_delivery_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'raw_response' => 'array',
    ];

    public function tripRequest()
    {
        return $this->belongsTo(TripRequest::class, 'shipment_id', 'id');
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
}
