<?php

namespace Modules\Gateways\Entities;

use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    protected $table = 'sms_logs';

    protected $fillable = [
        'gateway',
        'receiver',
        'message',
        'type',
        'status',
        'response',
        'error_message',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'error');
    }

    public function getMaskedReceiverAttribute(): string
    {
        return maskPhoneNumber($this->receiver);
    }

    public function getRedactedMessageAttribute(): string
    {
        if ($this->type === 'otp') {
            return '[OTP REDACTED]';
        }
        return redactOtpMessage($this->message);
    }
}
