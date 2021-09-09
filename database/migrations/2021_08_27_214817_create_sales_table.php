<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('receipt_number')->unique();
            $table->unsignedBigInteger('sale_time');
            $table->timestamps();
            $table->foreignId('counter_user_id')->nullable()->constrained();
            $table->foreignId('branch_id')->constrained();
            $table->foreignId('feedback_id')->nullable()->constrained();

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
        Schema::dropIfExists('sales');
    }
}
