<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCounterUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Your typical employee user
        Schema::create('counter_users', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->string('name', 255);
            $table->string('sname', 32);
            $table->unsignedInteger('number');
            $table->date('date_joined')->nullable();
            $table->date('last_updated')->nullable();
            $table->string('position', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('branch_id')->constrained();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('counter_users');
    }
}
