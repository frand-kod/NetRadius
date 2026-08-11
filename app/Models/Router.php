<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Database\Factories\RouterFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Router extends Model
{
    /** @use HasFactory<RouterFactory> */
    use HasFactory, LogsActivity;

    public $timestamps = false;

    protected $table = 'tbl_routers';

    /** @var array<int, string> */
    protected $fillable = [
        'name',
        'ip_address',
        'username',
        'password',
        'description',
        'coordinates',
        'status',
        'last_seen',
        'coverage',
        'enabled',
    ];

    /** @var array<int, string> */
    protected $hidden = [
        'password',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_seen' => 'datetime',
            'enabled' => 'boolean',
        ];
    }
}
