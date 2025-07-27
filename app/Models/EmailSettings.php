<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelSettings\Settings;

class EmailSettings extends Settings
{
    public string $seller_email;
    public bool $notifications_enabled;

    public static function group(): string
    {
        return 'email';
    }
}
