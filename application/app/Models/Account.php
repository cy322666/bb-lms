<?php

namespace App\Models;

use App\Models\Doc;
use App\Models\DocSetting;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'code',
        'zone',
        'state',
        'client_id',
        'work',
        'client_secret',
        'referer',
        'expires_in',
        'created_at',
        'token_type',
        'redirect_uri',
        'endpoint',
        'expires_tariff',
    ];

    protected $guarded = [];

    public function doc(): hasMany
    {
        return $this->hasMany(Doc::class);
    }

    public function docSetting(): BelongsTo
    {
        return $this->belongsTo(DocSetting::class);
    }
}
