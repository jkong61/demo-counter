<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    public $with = ['sale','loginsession'];

    public function loginsession()
    {
        return $this->belongsTo(LoginSession::class,'login_session_id');
    }

    public function counter()
    {
        return $this->belongsTo(Counter::class);
    }

    public function sale()
    {
        return $this->hasOne(Sale::class);
    }
}
