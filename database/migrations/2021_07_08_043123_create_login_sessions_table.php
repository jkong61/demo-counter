<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoginSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('login_sessions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('login_date_time')->nullable();
            $table->unsignedBigInteger('logout_date_time')->nullable();
            $table->foreignId('counter_id')->constrained();
            $table->foreignId('counter_user_id')->nullable()->constrained();
            $table->foreignId('branch_id')->constrained();

            // Mainly used assign temporary number incase user ID was not created first
            $table->unsignedBigInteger('temp_counter_user_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('login_sessions');
    }
}
