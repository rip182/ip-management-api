<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class InternetProtocolAddress extends Model
{
    /** @use HasFactory<\Database\Factories\InternetProtocolFactory> */
    use HasFactory;

    protected $guarded = [];

    public function User()
    {
        return $this->belongsTo(User::class);
    }
}
