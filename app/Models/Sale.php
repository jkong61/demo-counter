<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_number',
        'sale_time',
        'branch_id',
        'counter_user_id',
        'temp_counter_user_number'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function feedback()
    {
        return $this->belongsTo(Feedback::class);
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
