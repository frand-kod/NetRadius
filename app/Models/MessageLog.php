<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageLog extends Model
{
    const CREATED_AT = 'sent_at';

    const UPDATED_AT = null;

    protected $table = 'tbl_message_logs';

    protected $fillable = [
        'message_type',
        'recipient',
        'message_content',
        'status',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }
}
