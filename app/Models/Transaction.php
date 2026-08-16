<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    public $timestamps = false;

    protected $table = 'tbl_transactions';

    /** @var array<int, string> */
    protected $fillable = [
        'invoice',
        'username',
        'user_id',
        'plan_name',
        'price',
        'recharged_on',
        'recharged_time',
        'expiration',
        'time',
        'method',
        'type',
        'admin_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'recharged_on' => 'date:Y-m-d',
            'expiration' => 'date:Y-m-d',
        ];
    }
}
