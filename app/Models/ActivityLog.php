<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $table = 'tbl_logs';

    protected $fillable = ['date', 'type', 'description', 'userid', 'ip'];

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
        ];
    }
}
