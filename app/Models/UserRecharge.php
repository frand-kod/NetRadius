<?php

namespace App\Models;

use Database\Factories\UserRechargeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class UserRecharge extends Model
{
    /** @use HasFactory<UserRechargeFactory> */
    use HasFactory;

    protected $table = 'tbl_user_recharges';

    public $timestamps = false;

    protected $fillable = [
        'customer_id', 'username', 'plan_id', 'namebp', 'recharged_on', 'recharged_time',
        'expiration', 'time', 'status', 'method', 'type', 'admin_id',
    ];

    protected function casts(): array
    {
        return [
            'recharged_on' => 'date:Y-m-d',
            'expiration' => 'date:Y-m-d',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function isExpired(): bool
    {
        $expiresAt = Carbon::parse($this->expiration->toDateString().' '.$this->time);

        return $expiresAt->isPast();
    }
}
