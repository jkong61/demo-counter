<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    public function counter() {
        return $this->hasMany(Counter::class);
    }

    public function counteruser() {
        return $this->hasMany(CounterUser::class);
    }

    static function getIdByName(string $branch_name) {
        return Branch::where('name','=',$branch_name)->first();
    }
}
