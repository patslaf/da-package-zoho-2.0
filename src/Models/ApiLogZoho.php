<?php

namespace Patslaf\DigitalAcorn\Zoho20\Models;

use Illuminate\Database\Eloquent\Model;

class ApiLogZoho extends Model
{
    protected $table = 'api_logs_zoho';

    protected $guarded = [];

    protected $casts = [
        'data' => 'json',
        'response' => 'json',
    ];
}
