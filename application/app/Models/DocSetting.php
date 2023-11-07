<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'target_login',
        'target_pass',
        'target_sender',
        'account_id',
        'subdomain',
        'text_sms',
        'status_id_confirm'
    ];
}
