<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Partner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'company_name',
        'email',
        'phone',
        'api_key',
        'secret_key',
        'webhook_url',
        'status',
        'permissions',
        'last_active_at',
    ];

    protected $casts = [
        'permissions' => 'array',
        'last_active_at' => 'datetime',
    ];

    public static function generateApiKey(): string
    {
        return 'xp_' . Str::random(40);
    }

    public static function generateSecretKey(): string
    {
        return 'xs_' . Str::random(60);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
