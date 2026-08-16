<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Database\Factories\PlanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Plan extends Model
{
    /** @use HasFactory<PlanFactory> */
    use HasFactory, LogsActivity;

    public $timestamps = false;

    protected $table = 'tbl_plans';

    /** @var array<int, string> */
    protected $fillable = [
        'name_plan',
        'id_bw',
        'price',
        'type',
        'typebp',
        'limit_type',
        'time_limit',
        'time_unit',
        'data_limit',
        'data_unit',
        'validity',
        'validity_unit',
        'shared_users',
        'enabled',
        'device',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
        ];
    }

    public function bandwidth(): BelongsTo
    {
        return $this->belongsTo(Bandwidth::class, 'id_bw');
    }
}
