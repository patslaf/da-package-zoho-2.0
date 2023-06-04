<?php

namespace Patslaf\DigitalAcorn\Zoho20\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZohoOauthtoken extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'zoho_oauthtoken';
}
