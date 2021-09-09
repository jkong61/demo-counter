<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'login_date_time',
        'logout_date_time',
        'counter_id',
        'counter_user_id'
    ];

    public $with = ['counter_user', 'counter', 'branch'];

    public function counter_user()
    {
        return $this->belongsTo(CounterUser::class);
    }

    public function counter()
    {
        return $this->belongsTo(Counter::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function feedback()
    {
        return $this->hasMany(Feedback::class);
    }

    public function remapTemporaryCounterUser()
    {
        if (is_null($this->counter_user_id) 
            && !is_null($this->temp_counter_user_number)) 
        {
            // May potentially raise Foreign Key Exception, if the Counter user still does not exist
            $counter_user = CounterUser::getIdFromNumberBranch($this->temp_counter_user_number, $this->branch->id);
            $this->counter_user_id = $counter_user->id;
            $this->temp_counter_user_number = null;
        }
    }
}
