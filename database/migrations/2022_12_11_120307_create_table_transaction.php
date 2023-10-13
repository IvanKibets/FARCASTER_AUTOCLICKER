<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableTransaction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id')->nullable();
            $table->bigInteger('price')->nullable();
            $table->string('description')->nullable();
            $table->integer('user_id')->nullable();
            $table->boolean('status')->default(0)->nullable();
            $table->text('api_response_before')->nullable();
            $table->text('api_response_after')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
