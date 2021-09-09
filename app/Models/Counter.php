<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Counter extends Model
{
    use HasFactory;

    public $with = ['branch'];

    protected $fillable = [
        'is_active'
    ];

    protected $attributes = [
        'is_active' => false,
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function loginsession()
    {
        return $this->hasMany(LoginSession::class);
    }

    public function feedback()
    {
        return $this->hasManyThrough(Feedback::class, LoginSession::class);
    }

    public function feedback_counter()
    {
        return $this->hasMany(Feedback::class);
    }

    static function getIdByBranchIdAndNumber(int $branch_id, int $counter_number)
    {
        return Counter::where('number','=', $counter_number)->where('branch_id','=', $branch_id)->first();
    }
}
