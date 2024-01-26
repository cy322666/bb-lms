<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doc extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_sms',
        'status',
        'phone',
        'lead_id',
        'contact_id',
        'send_code',
    ];
}
