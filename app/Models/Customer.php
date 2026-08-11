<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Customer extends Authenticatable
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory, LogsActivity;

    const UPDATED_AT = null;

    protected $table = 'tbl_customers';

    /** @var array<int, string> */
    protected $fillable = [
        'username',
        'password',
        'photo',
        'pppoe_username',
        'pppoe_password',
        'pppoe_ip',
        'fullname',
        'address',
        'city',
        'district',
        'state',
        'zip',
        'phonenumber',
        'email',
        'coordinates',
        'account_type',
        'balance',
        'service_type',
        'auto_renewal',
        'status',
        'created_by',
        'last_login',
    ];

    /** @var array<int, string> */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // NOT hashed: RADIUS PAP/CHAP verification needs the plaintext
            // password (same as the legacy system) to compare/challenge
            // against. Portal login (task #5) must compare plain strings,
            // not Hash::check().
            'balance' => 'decimal:2',
            'auto_renewal' => 'boolean',
            'created_at' => 'datetime',
            'last_login' => 'datetime',
        ];
    }
}
