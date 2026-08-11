<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppConfig extends Model
{
    public $timestamps = false;

    protected $table = 'tbl_appconfig';

    protected $fillable = ['setting', 'value'];

    public static function get(string $key, ?string $default = null): ?string
    {
        return static::query()->where('setting', $key)->value('value') ?? $default;
    }

    public static function set(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(['setting' => $key], ['value' => $value]);
    }
}
