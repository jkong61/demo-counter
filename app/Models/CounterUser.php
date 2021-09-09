<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CounterUser extends Model
{
    use HasFactory, SoftDeletes;

    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'sname',
        'position',
        'number',
        'branch_id'
    ];

    public function loginsession()
    {
        return $this->hasMany(LoginSession::class);
    }

    public function feedback()
    {
        return $this->hasManyThrough(Feedback::class, LoginSession::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    static function getIdFromNumberBranch(int $user_number, int $branch_id)
    {
        return CounterUser::where('number','=', $user_number)
            ->where('branch_id','=', $branch_id)
            ->firstOrFail();
    }
}
